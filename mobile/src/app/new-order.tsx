/**
 * Prince Distributors B2B — Ultra Fast 3-Step Mobile Order Flow
 * Step 1: Select Customer -> Step 2: Add Products -> Step 3: Review & Confirm
 */

import React, { useState, useEffect, useMemo } from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  SafeAreaView,
  Alert,
  TextInput,
} from 'react-native';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { useTheme } from '../lib/theme';
import { ApiClient } from '../lib/api';
import { Card, Button, Badge, SearchBar, Typography, Header } from '../components/ui';
import { Customer, Product } from '../types';

interface CartItem {
  product: Product;
  package_type: 'unit' | 'box';
  quantity: number;
  unit_price: number;
  subtotal: number;
}

export default function NewOrderScreen() {
  const { colors, t } = useTheme();
  const router = useRouter();
  const params = useLocalSearchParams<{
    preselected_customer_id?: string;
    preselected_customer_name?: string;
    add_product_id?: string;
  }>();

  // Step state: 1 (Customer) | 2 (Products) | 3 (Review)
  const [step, setStep] = useState<1 | 2 | 3>(1);

  // Data states
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [customerSearch, setCustomerSearch] = useState('');
  const [productSearch, setProductSearch] = useState('');
  const [notes, setNotes] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    // Load customers and products in parallel
    Promise.all([ApiClient.getCustomers(), ApiClient.getProducts()]).then(([cList, pList]) => {
      setCustomers(cList);
      setProducts(pList);

      // Handle preselection
      if (params.preselected_customer_id) {
        const found = cList.find((c) => c.id === Number(params.preselected_customer_id));
        if (found) {
          setSelectedCustomer(found);
          setStep(2);
        }
      }

      if (params.add_product_id) {
        const prod = pList.find((p) => p.id === Number(params.add_product_id));
        if (prod) {
          addToCart(prod, 'box', 1);
        }
      }
    });
  }, [params.preselected_customer_id, params.add_product_id]);

  // Cart operations
  const addToCart = (product: Product, package_type: 'unit' | 'box', qtyDelta: number) => {
    setCart((prev) => {
      const idx = prev.findIndex(
        (item) => item.product.id === product.id && item.package_type === package_type
      );
      const price = package_type === 'box' ? product.box_price : product.unit_price;

      if (idx >= 0) {
        const newQty = prev[idx].quantity + qtyDelta;
        if (newQty <= 0) {
          return prev.filter((_, i) => i !== idx);
        }
        const updated = [...prev];
        updated[idx] = {
          ...updated[idx],
          quantity: newQty,
          subtotal: +(newQty * price).toFixed(2),
        };
        return updated;
      } else if (qtyDelta > 0) {
        return [
          ...prev,
          {
            product,
            package_type,
            quantity: qtyDelta,
            unit_price: price,
            subtotal: +(qtyDelta * price).toFixed(2),
          },
        ];
      }
      return prev;
    });
  };

  const getProductCartQty = (productId: number, package_type: 'unit' | 'box') => {
    const item = cart.find(
      (c) => c.product.id === productId && c.package_type === package_type
    );
    return item ? item.quantity : 0;
  };

  // Calculations
  const subtotal = useMemo(() => {
    return +(cart.reduce((sum, item) => sum + item.subtotal, 0)).toFixed(2);
  }, [cart]);

  const tax = useMemo(() => {
    return +(subtotal * 0.18).toFixed(2);
  }, [subtotal]);

  const total = useMemo(() => {
    return +(subtotal + tax).toFixed(2);
  }, [subtotal, tax]);

  // Credit verification
  const creditAvailable = selectedCustomer
    ? selectedCustomer.credit_limit - selectedCustomer.outstanding_balance
    : 0;
  const isCreditExceeded = selectedCustomer && total > creditAvailable;

  // Handle final submission
  const handleConfirmOrder = async () => {
    if (!selectedCustomer) {
      Alert.alert('Error', 'Seleccione un cliente comercial');
      setStep(1);
      return;
    }
    if (cart.length === 0) {
      Alert.alert('Error', 'Debe agregar al menos un producto al pedido');
      setStep(2);
      return;
    }

    setSubmitting(true);
    try {
      const res = await ApiClient.createOrder({
        customer_id: selectedCustomer.id,
        customer_name: selectedCustomer.business_name,
        items: cart.map((item) => ({
          product_id: item.product.id,
          product_name: item.product.name,
          package_type: item.package_type,
          quantity: item.quantity,
          unit_price: item.unit_price,
          subtotal: item.subtotal,
        })),
        notes,
      });

      if (res.is_offline) {
        Alert.alert(
          'Guardado sin Conexión',
          `El pedido ${res.order.order_number} se guardó localmente como borrador y se sincronizará automáticamente al restablecerse la red.`,
          [{ text: 'Aceptar', onPress: () => router.replace('/orders' as any) }]
        );
      } else {
        Alert.alert(
          '¡Pedido Confirmado!',
          `Pedido ${res.order.order_number} registrado exitosamente en el sistema central B2B.`,
          [{ text: 'Ver Pedidos', onPress: () => router.replace('/orders' as any) }]
        );
      }
    } catch (err: any) {
      Alert.alert('Error de Emisión', err.message || 'No se pudo emitir el pedido.');
    } finally {
      setSubmitting(false);
    }
  };

  // Filtered lists
  const filteredCustomers = useMemo(() => {
    if (!customerSearch) return customers;
    const q = customerSearch.toLowerCase();
    return customers.filter(
      (c) =>
        c.business_name.toLowerCase().includes(q) ||
        (c.commercial_name && c.commercial_name.toLowerCase().includes(q)) ||
        c.tax_id.includes(q)
    );
  }, [customers, customerSearch]);

  const filteredProducts = useMemo(() => {
    if (!productSearch) return products;
    const q = productSearch.toLowerCase();
    return products.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q) ||
        p.brand.toLowerCase().includes(q)
    );
  }, [products, productSearch]);

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Modal Top Bar */}
      <Header
        title={
          step === 1
            ? '1. Seleccionar Cliente'
            : step === 2
            ? '2. Agregar Productos'
            : '3. Confirmar Pedido'
        }
        subtitle={
          selectedCustomer
            ? `Cliente: ${selectedCustomer.business_name}`
            : 'Cotización B2B Express'
        }
        onBack={() => {
          if (step > 1) setStep((s) => (s - 1) as any);
          else router.back();
        }}
        rightAction={
          <TouchableOpacity onPress={() => router.back()} style={{ padding: 6 }}>
            <Text style={{ fontSize: 18, color: colors.textMuted }}>✕</Text>
          </TouchableOpacity>
        }
      />

      {/* Progress Stepper Bar */}
      <View
        style={{
          flexDirection: 'row',
          backgroundColor: colors.card,
          paddingHorizontal: 20,
          paddingBottom: 10,
          gap: 6,
        }}
      >
        <View
          style={{
            flex: 1,
            height: 4,
            borderRadius: 2,
            backgroundColor: step >= 1 ? colors.primary : colors.border,
          }}
        />
        <View
          style={{
            flex: 1,
            height: 4,
            borderRadius: 2,
            backgroundColor: step >= 2 ? colors.primary : colors.border,
          }}
        />
        <View
          style={{
            flex: 1,
            height: 4,
            borderRadius: 2,
            backgroundColor: step >= 3 ? colors.primary : colors.border,
          }}
        />
      </View>

      {/* ================= STEP 1: SELECT CUSTOMER ================= */}
      {step === 1 && (
        <View style={{ flex: 1, padding: 18 }}>
          <SearchBar
            value={customerSearch}
            onChangeText={setCustomerSearch}
            placeholder="Buscar por cliente, RUC o territorio..."
            style={{ marginBottom: 12 }}
          />

          <ScrollView showsVerticalScrollIndicator={false}>
            {filteredCustomers.map((cust) => {
              const isSelected = selectedCustomer?.id === cust.id;
              const avail = cust.credit_limit - cust.outstanding_balance;
              const isRisk = avail < 1000;

              return (
                <Card
                  key={cust.id}
                  onPress={() => {
                    setSelectedCustomer(cust);
                    setStep(2);
                  }}
                  style={{
                    marginBottom: 10,
                    borderColor: isSelected ? colors.primary : colors.border,
                    borderWidth: isSelected ? 2 : 1,
                  }}
                >
                  <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                    <View style={{ flex: 1, paddingRight: 8 }}>
                      <Text style={Typography.h3(colors.text)}>{cust.business_name}</Text>
                      <Text style={Typography.caption(colors.textMuted)}>
                        RUC {cust.tax_id} • {cust.territory}
                      </Text>
                    </View>
                    <Badge
                      label={`$${avail.toFixed(0)} disp.`}
                      variant={isRisk ? 'warning' : 'success'}
                    />
                  </View>
                </Card>
              );
            })}
          </ScrollView>
        </View>
      )}

      {/* ================= STEP 2: ADD PRODUCTS TO CART ================= */}
      {step === 2 && (
        <View style={{ flex: 1 }}>
          <View style={{ paddingHorizontal: 18, paddingTop: 10, paddingBottom: 6 }}>
            <SearchBar
              value={productSearch}
              onChangeText={setProductSearch}
              placeholder="Buscar SKU, producto o marca..."
            />
          </View>

          <ScrollView contentContainerStyle={{ padding: 18, paddingBottom: 100 }}>
            {filteredProducts.map((prod) => {
              const unitQty = getProductCartQty(prod.id, 'unit');
              const boxQty = getProductCartQty(prod.id, 'box');

              return (
                <Card key={prod.id} style={{ marginBottom: 12 }}>
                  <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                    <View style={{ flex: 1, paddingRight: 8 }}>
                      <Text style={{ fontSize: 11, fontWeight: '700', color: colors.primary }}>
                        {prod.sku} • {prod.brand}
                      </Text>
                      <Text style={Typography.h3(colors.text)} numberOfLines={1}>
                        {prod.name}
                      </Text>
                    </View>
                    <Text style={Typography.caption(colors.textMuted)}>
                      Stock: {prod.current_stock}
                    </Text>
                  </View>

                  {/* Quantity Selectors for Box & Unit */}
                  <View style={{ marginTop: 12, gap: 8 }}>
                    {/* Box selector */}
                    <View
                      style={{
                        flexDirection: 'row',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        backgroundColor: colors.background,
                        padding: 8,
                        borderRadius: 10,
                      }}
                    >
                      <View>
                        <Text style={{ fontSize: 13, fontWeight: '600', color: colors.text }}>
                          📦 Por Caja ({prod.units_per_box}u)
                        </Text>
                        <Text style={{ fontSize: 13, fontWeight: '700', color: colors.primary }}>
                          ${prod.box_price.toFixed(2)}
                        </Text>
                      </View>

                      <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                        <TouchableOpacity
                          onPress={() => addToCart(prod, 'box', -1)}
                          style={{
                            width: 32,
                            height: 32,
                            borderRadius: 16,
                            backgroundColor: colors.card,
                            alignItems: 'center',
                            justifyContent: 'center',
                            borderWidth: 1,
                            borderColor: colors.border,
                          }}
                        >
                          <Text style={{ fontSize: 16, color: colors.text, fontWeight: '700' }}>-</Text>
                        </TouchableOpacity>

                        <Text style={{ fontSize: 15, fontWeight: '700', minWidth: 20, textAlign: 'center', color: colors.text }}>
                          {boxQty}
                        </Text>

                        <TouchableOpacity
                          onPress={() => addToCart(prod, 'box', 1)}
                          style={{
                            width: 32,
                            height: 32,
                            borderRadius: 16,
                            backgroundColor: colors.primary,
                            alignItems: 'center',
                            justifyContent: 'center',
                          }}
                        >
                          <Text style={{ fontSize: 16, color: '#FFFFFF', fontWeight: '700' }}>+</Text>
                        </TouchableOpacity>
                      </View>
                    </View>

                    {/* Unit selector */}
                    <View
                      style={{
                        flexDirection: 'row',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        backgroundColor: colors.background,
                        padding: 8,
                        borderRadius: 10,
                      }}
                    >
                      <View>
                        <Text style={{ fontSize: 13, fontWeight: '600', color: colors.text }}>
                          🥫 Unidad Suelta
                        </Text>
                        <Text style={{ fontSize: 13, fontWeight: '700', color: colors.primary }}>
                          ${prod.unit_price.toFixed(2)}
                        </Text>
                      </View>

                      <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                        <TouchableOpacity
                          onPress={() => addToCart(prod, 'unit', -1)}
                          style={{
                            width: 32,
                            height: 32,
                            borderRadius: 16,
                            backgroundColor: colors.card,
                            alignItems: 'center',
                            justifyContent: 'center',
                            borderWidth: 1,
                            borderColor: colors.border,
                          }}
                        >
                          <Text style={{ fontSize: 16, color: colors.text, fontWeight: '700' }}>-</Text>
                        </TouchableOpacity>

                        <Text style={{ fontSize: 15, fontWeight: '700', minWidth: 20, textAlign: 'center', color: colors.text }}>
                          {unitQty}
                        </Text>

                        <TouchableOpacity
                          onPress={() => addToCart(prod, 'unit', 1)}
                          style={{
                            width: 32,
                            height: 32,
                            borderRadius: 16,
                            backgroundColor: colors.primary,
                            alignItems: 'center',
                            justifyContent: 'center',
                          }}
                        >
                          <Text style={{ fontSize: 16, color: '#FFFFFF', fontWeight: '700' }}>+</Text>
                        </TouchableOpacity>
                      </View>
                    </View>
                  </View>
                </Card>
              );
            })}
          </ScrollView>

          {/* Bottom Floating Bar */}
          <View
            style={{
              position: 'absolute',
              bottom: 0,
              left: 0,
              right: 0,
              backgroundColor: colors.card,
              borderTopWidth: 1,
              borderTopColor: colors.border,
              paddingHorizontal: 20,
              paddingVertical: 14,
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              shadowColor: '#000',
              shadowOffset: { width: 0, height: -3 },
              shadowOpacity: 0.1,
              shadowRadius: 6,
              elevation: 10,
            }}
          >
            <View>
              <Text style={Typography.caption(colors.textMuted)}>
                {cart.reduce((s, i) => s + i.quantity, 0)} ítems en carrito
              </Text>
              <Text style={{ fontSize: 20, fontWeight: '800', color: colors.primary }}>
                ${total.toFixed(2)}
              </Text>
            </View>

            <Button
              title="Continuar ›"
              disabled={cart.length === 0}
              onPress={() => setStep(3)}
              size="lg"
            />
          </View>
        </View>
      )}

      {/* ================= STEP 3: REVIEW & CONFIRM ================= */}
      {step === 3 && (
        <View style={{ flex: 1 }}>
          <ScrollView contentContainerStyle={{ padding: 18, paddingBottom: 110 }}>
            {/* Customer Summary Card */}
            <Card style={{ marginBottom: 14 }}>
              <Text style={Typography.caption(colors.textMuted)}>CLIENTE COMERCIAL</Text>
              <Text style={[Typography.h3(colors.text), { marginTop: 4 }]}>
                {selectedCustomer?.business_name}
              </Text>
              <Text style={Typography.caption(colors.textMuted)}>
                RUC: {selectedCustomer?.tax_id} • Plazo: {selectedCustomer?.payment_terms_days} días
              </Text>

              {/* Credit Risk Warning */}
              {isCreditExceeded && (
                <View
                  style={{
                    backgroundColor: colors.danger + '1A',
                    padding: 10,
                    borderRadius: 8,
                    marginTop: 10,
                    borderLeftWidth: 3,
                    borderLeftColor: colors.danger,
                  }}
                >
                  <Text style={{ fontSize: 12, fontWeight: '700', color: colors.danger }}>
                    ⚠️ Advertencia de Crédito
                  </Text>
                  <Text style={{ fontSize: 12, color: colors.danger, marginTop: 2 }}>
                    El total (${total.toFixed(2)}) supera el crédito disponible (${creditAvailable.toFixed(2)}). Requerirá aprobación de supervisor.
                  </Text>
                </View>
              )}
            </Card>

            {/* Line Items List */}
            <Text style={[Typography.h3(colors.text), { marginBottom: 8 }]}>
              Detalle de Productos ({cart.length})
            </Text>

            {cart.map((item, idx) => (
              <Card key={idx} style={{ marginBottom: 8, padding: 12 }}>
                <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                  <View style={{ flex: 1 }}>
                    <Text style={[Typography.body(colors.text), { fontWeight: '600' }]} numberOfLines={1}>
                      {item.product.name}
                    </Text>
                    <Text style={Typography.caption(colors.textMuted)}>
                      {item.quantity} x {item.package_type === 'box' ? `Caja (${item.product.units_per_box}u)` : 'Unidad'} a ${item.unit_price.toFixed(2)}
                    </Text>
                  </View>
                  <Text style={{ fontWeight: '700', fontSize: 15, color: colors.text }}>
                    ${item.subtotal.toFixed(2)}
                  </Text>
                </View>
              </Card>
            ))}

            {/* Notes Input */}
            <Text style={[Typography.h3(colors.text), { marginTop: 14, marginBottom: 6 }]}>
              Instrucciones de Entrega (Opcional)
            </Text>
            <TextInput
              value={notes}
              onChangeText={setNotes}
              placeholder="Ej. Entregar antes de las 11:00 am en puerta lateral"
              placeholderTextColor={colors.textMuted}
              style={{
                backgroundColor: colors.card,
                borderRadius: 12,
                padding: 12,
                borderWidth: 1,
                borderColor: colors.border,
                color: colors.text,
                fontSize: 14,
              }}
            />

            {/* Financial Breakdown */}
            <Card style={{ marginTop: 16 }}>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 }}>
                <Text style={Typography.body(colors.textMuted)}>Subtotal Neto</Text>
                <Text style={Typography.body(colors.text)}>${subtotal.toFixed(2)}</Text>
              </View>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 }}>
                <Text style={Typography.body(colors.textMuted)}>Impuesto IGV (18%)</Text>
                <Text style={Typography.body(colors.text)}>${tax.toFixed(2)}</Text>
              </View>
              <View
                style={{
                  height: 1,
                  backgroundColor: colors.border,
                  marginVertical: 8,
                }}
              />
              <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                <Text style={[Typography.h3(colors.text), { fontSize: 18 }]}>Total a Facturar</Text>
                <Text style={[Typography.h2(colors.primary), { fontSize: 20 }]}>${total.toFixed(2)}</Text>
              </View>
            </Card>
          </ScrollView>

          {/* Fixed Bottom Action */}
          <View
            style={{
              position: 'absolute',
              bottom: 0,
              left: 0,
              right: 0,
              backgroundColor: colors.card,
              borderTopWidth: 1,
              borderTopColor: colors.border,
              padding: 16,
            }}
          >
            <Button
              title="Confirmar y Enviar Pedido"
              loading={submitting}
              onPress={handleConfirmOrder}
              size="lg"
            />
          </View>
        </View>
      )}
    </SafeAreaView>
  );
}
