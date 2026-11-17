/**
 * Prince Distributors B2B — Tab 3: Pedidos (Order History, Status Tracking & Reordering)
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
import { Card, Button, Badge, Skeleton, Typography } from '../../components/ui';
import { Order } from '../../types';

export default function OrdersScreen() {
  const { colors, t } = useTheme();
  const router = useRouter();

  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedStatus, setSelectedStatus] = useState('all');
  const [expandedId, setExpandedId] = useState<number | null>(null);

  const loadOrders = async () => {
    try {
      const data = await ApiClient.getOrders();
      setOrders(data);
    } catch {
      // Fallback inside ApiClient
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadOrders();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadOrders();
  };

  const statusFilters = [
    { id: 'all', label: 'Todos' },
    { id: 'draft', label: 'Borradores' },
    { id: 'confirmed', label: 'Confirmados' },
    { id: 'processing', label: 'En Preparación' },
    { id: 'dispatched', label: 'Despachados' },
    { id: 'invoiced', label: 'Facturados' },
  ];

  const filteredOrders = useMemo(() => {
    if (selectedStatus === 'all') return orders;
    return orders.filter((o) => o.status === selectedStatus);
  }, [orders, selectedStatus]);

  const getStatusBadge = (status: Order['status'], isDraft?: boolean) => {
    if (isDraft) {
      return <Badge label="Borrador Local" variant="warning" />;
    }
    switch (status) {
      case 'confirmed':
        return <Badge label="Confirmado" variant="success" />;
      case 'processing':
        return <Badge label="En Proceso" variant="info" />;
      case 'dispatched':
        return <Badge label="Despachado" variant="info" />;
      case 'invoiced':
        return <Badge label="Facturado" variant="neutral" />;
      case 'cancelled':
        return <Badge label="Cancelado" variant="danger" />;
      default:
        return <Badge label="Pendiente" variant="warning" />;
    }
  };

  const handleReorder = (order: Order) => {
    router.push({
      pathname: '/new-order',
      params: {
        preselected_customer_id: order.customer_id,
        preselected_customer_name: order.customer_name,
        reorder_from: order.order_number,
      },
    } as any);
  };

  const renderItem = ({ item }: { item: Order }) => {
    const isExpanded = expandedId === item.id;

    return (
      <Card style={{ marginBottom: 12 }}>
        <TouchableOpacity
          activeOpacity={0.7}
          onPress={() => setExpandedId(isExpanded ? null : item.id)}
        >
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
            <View style={{ flex: 1, paddingRight: 8 }}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                <Text style={{ fontWeight: '700', fontSize: 15, color: colors.primary }}>
                  {item.order_number}
                </Text>
                {item.is_offline_draft ? (
                  <Text style={{ fontSize: 11, color: colors.warning, fontWeight: '600' }}>
                    [OFFLINE]
                  </Text>
                ) : null}
              </View>

              <Text style={[Typography.h3(colors.text), { marginTop: 2 }]} numberOfLines={1}>
                {item.customer_name}
              </Text>
              <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
                📅 {item.created_at} • {item.items_count} productos
              </Text>
            </View>

            <View style={{ alignItems: 'flex-end' }}>
              {getStatusBadge(item.status, item.is_offline_draft)}
              <Text style={[Typography.h3(colors.text), { marginTop: 6, fontWeight: '700' }]}>
                ${item.total_amount.toFixed(2)}
              </Text>
            </View>
          </View>
        </TouchableOpacity>

        {isExpanded && (
          <View
            style={{
              marginTop: 12,
              paddingTop: 12,
              borderTopWidth: 1,
              borderTopColor: colors.border,
            }}
          >
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 }}>
              <Text style={Typography.caption(colors.textMuted)}>Subtotal:</Text>
              <Text style={Typography.caption(colors.text)}>${item.subtotal.toFixed(2)}</Text>
            </View>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 }}>
              <Text style={Typography.caption(colors.textMuted)}>IGV (18%):</Text>
              <Text style={Typography.caption(colors.text)}>${item.tax_amount.toFixed(2)}</Text>
            </View>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 }}>
              <Text style={[Typography.caption(colors.text), { fontWeight: '700' }]}>Total Facturable:</Text>
              <Text style={[Typography.caption(colors.primary), { fontWeight: '700' }]}>
                ${item.total_amount.toFixed(2)}
              </Text>
            </View>
            {item.notes ? (
              <Text style={[Typography.caption(colors.textMuted), { fontStyle: 'italic', marginBottom: 8 }]}>
                Nota: "{item.notes}"
              </Text>
            ) : null}
          </View>
        )}

        {/* Action Row */}
        <View
          style={{
            flexDirection: 'row',
            justifyContent: 'flex-end',
            gap: 10,
            marginTop: 10,
            paddingTop: 8,
            borderTopWidth: 1,
            borderTopColor: colors.border,
          }}
        >
          <Button
            title="Repetir Pedido"
            variant="secondary"
            size="sm"
            icon="🔄"
            onPress={() => handleReorder(item)}
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
          flexDirection: 'row',
          justifyContent: 'space-between',
          alignItems: 'center',
        }}
      >
        <Text style={Typography.h2(colors.text)}>📦 {t('orders_title')}</Text>
        <Button
          title="+ Nuevo"
          size="sm"
          onPress={() => router.push('/new-order' as any)}
        />
      </View>

      {/* Status Filter Chips */}
      <View style={{ backgroundColor: colors.card, paddingBottom: 10 }}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={{ paddingHorizontal: 18, gap: 8 }}
        >
          {statusFilters.map((st) => {
            const isSelected = selectedStatus === st.id;
            return (
              <TouchableOpacity
                key={st.id}
                onPress={() => setSelectedStatus(st.id)}
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
                  {st.label}
                </Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>

      {/* List */}
      {loading ? (
        <View style={{ padding: 18, gap: 12 }}>
          <Skeleton height={110} />
          <Skeleton height={110} />
          <Skeleton height={110} />
        </View>
      ) : (
        <FlatList
          data={filteredOrders}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderItem}
          contentContainerStyle={{ padding: 18, paddingBottom: 30 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
          ListEmptyComponent={
            <View style={{ alignItems: 'center', marginTop: 40, padding: 20 }}>
              <Text style={{ fontSize: 40, marginBottom: 12 }}>📦</Text>
              <Text style={Typography.h3(colors.text)}>No hay pedidos registrados</Text>
              <Text style={[Typography.caption(colors.textMuted), { textAlign: 'center', marginTop: 4, marginBottom: 16 }]}>
                Inicie una nueva cotización comercial con el botón superior.
              </Text>
              <Button
                title="Crear Primer Pedido"
                onPress={() => router.push('/new-order' as any)}
              />
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}
