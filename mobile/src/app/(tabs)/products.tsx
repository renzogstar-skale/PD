/**
 * Prince Distributors B2B — Tab 4: Productos (Catalog, Prices by Unit/Box & Stock)
 */

import React, { useState, useEffect, useMemo } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  SafeAreaView,
  ScrollView,
  RefreshControl,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../../lib/theme';
import { ApiClient } from '../../lib/api';
import { Card, Button, Badge, SearchBar, Skeleton, Typography } from '../../components/ui';
import { Product } from '../../types';

export default function ProductsScreen() {
  const { colors, t } = useTheme();
  const router = useRouter();

  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('Todos');

  const categories = ['Todos', 'Bebidas', 'Lácteos', 'Abarrotes', 'Limpieza'];

  const loadProducts = async () => {
    try {
      const data = await ApiClient.getProducts();
      setProducts(data);
    } catch {
      // Handled inside ApiClient
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadProducts();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadProducts();
  };

  const filteredProducts = useMemo(() => {
    return products.filter((p) => {
      const q = search.toLowerCase();
      const matchSearch =
        !search ||
        p.name.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q) ||
        p.brand.toLowerCase().includes(q);

      const matchCategory =
        selectedCategory === 'Todos' || p.category === selectedCategory;

      return matchSearch && matchCategory;
    });
  }, [products, search, selectedCategory]);

  const renderProduct = ({ item }: { item: Product }) => {
    const isLowStock = item.current_stock < 50;

    return (
      <Card style={{ marginBottom: 12 }}>
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
          <View style={{ flex: 1, paddingRight: 8 }}>
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
              <Badge label={item.category} variant="info" />
              <Text style={{ fontSize: 11, color: colors.textMuted, fontWeight: '600' }}>
                {item.sku}
              </Text>
            </View>
            <Text style={[Typography.h3(colors.text), { marginTop: 4 }]}>
              {item.name}
            </Text>
            <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
              Marca: {item.brand} • Factor: {item.units_per_box} uds/caja
            </Text>
          </View>

          <Badge
            label={`${item.current_stock} disp.`}
            variant={isLowStock ? 'warning' : 'success'}
          />
        </View>

        {/* Price Breakdown: Unit vs Box */}
        <View
          style={{
            flexDirection: 'row',
            backgroundColor: colors.background,
            borderRadius: 10,
            padding: 10,
            marginTop: 10,
            gap: 12,
          }}
        >
          <View style={{ flex: 1 }}>
            <Text style={Typography.caption(colors.textMuted)}>Precio Unitario</Text>
            <Text style={{ fontSize: 16, fontWeight: '700', color: colors.primary, marginTop: 2 }}>
              ${item.unit_price.toFixed(2)}
            </Text>
          </View>
          <View style={{ width: 1, backgroundColor: colors.border }} />
          <View style={{ flex: 1 }}>
            <Text style={Typography.caption(colors.textMuted)}>Precio por Caja ({item.units_per_box}u)</Text>
            <Text style={{ fontSize: 16, fontWeight: '700', color: colors.primary, marginTop: 2 }}>
              ${item.box_price.toFixed(2)}
            </Text>
          </View>
        </View>

        {/* Add to order action */}
        <View style={{ marginTop: 10, alignItems: 'flex-end' }}>
          <Button
            title="Agregar a Pedido"
            variant="secondary"
            size="sm"
            icon="🛒"
            onPress={() => {
              router.push({
                pathname: '/new-order',
                params: { add_product_id: item.id },
              } as any);
            }}
          />
        </View>
      </Card>
    );
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Header */}
      <View
        style={{
          paddingHorizontal: 18,
          paddingTop: 14,
          paddingBottom: 10,
          backgroundColor: colors.card,
          borderBottomWidth: 1,
          borderBottomColor: colors.border,
        }}
      >
        <Text style={[Typography.h2(colors.text), { marginBottom: 10 }]}>
          🏷️ {t('products_title')}
        </Text>
        <SearchBar
          value={search}
          onChangeText={setSearch}
          placeholder={t('search_products')}
        />

        {/* Categories Horizontal Filter */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={{ marginTop: 10 }}
          contentContainerStyle={{ gap: 8 }}
        >
          {categories.map((cat) => {
            const isSelected = selectedCategory === cat;
            return (
              <TouchableOpacity
                key={cat}
                onPress={() => setSelectedCategory(cat)}
                style={{
                  paddingHorizontal: 12,
                  paddingVertical: 6,
                  borderRadius: 18,
                  backgroundColor: isSelected ? colors.primary : colors.background,
                  borderWidth: 1,
                  borderColor: isSelected ? colors.primary : colors.border,
                }}
              >
                <Text
                  style={{
                    fontSize: 12,
                    fontWeight: isSelected ? '700' : '500',
                    color: isSelected ? '#FFFFFF' : colors.textMuted,
                  }}
                >
                  {cat}
                </Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>

      {/* List */}
      {loading ? (
        <View style={{ padding: 18, gap: 12 }}>
          <Skeleton height={130} />
          <Skeleton height={130} />
          <Skeleton height={130} />
        </View>
      ) : (
        <FlatList
          data={filteredProducts}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderProduct}
          contentContainerStyle={{ padding: 18, paddingBottom: 30 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
          ListEmptyComponent={
            <View style={{ alignItems: 'center', marginTop: 40, padding: 20 }}>
              <Text style={{ fontSize: 40, marginBottom: 12 }}>🔍</Text>
              <Text style={Typography.h3(colors.text)}>Producto no encontrado</Text>
              <Text style={[Typography.caption(colors.textMuted), { textAlign: 'center', marginTop: 4 }]}>
                Intente con otro código o término de búsqueda.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}
