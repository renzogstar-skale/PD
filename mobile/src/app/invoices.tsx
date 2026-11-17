/**
 * Prince Distributors B2B — Facturación, Cuentas por Cobrar & Pagos
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  SafeAreaView,
  RefreshControl,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../lib/theme';
import { ApiClient } from '../lib/api';
import { Card, Badge, Header, Skeleton, Typography } from '../components/ui';
import { Invoice } from '../types';

export default function InvoicesScreen() {
  const { colors } = useTheme();
  const router = useRouter();

  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const data = await ApiClient.getInvoices();
      setInvoices(data);
    } catch {
      // ApiClient handles fallback
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const getStatusBadge = (status: Invoice['status']) => {
    switch (status) {
      case 'paid':
        return <Badge label="Pagada" variant="success" />;
      case 'partial':
        return <Badge label="Pago Parcial" variant="warning" />;
      case 'overdue':
        return <Badge label="Vencida" variant="danger" />;
      default:
        return <Badge label="Pendiente" variant="info" />;
    }
  };

  const renderInvoice = ({ item }: { item: Invoice }) => (
    <Card style={{ marginBottom: 12 }}>
      <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
        <View style={{ flex: 1, paddingRight: 8 }}>
          <Text style={{ fontSize: 15, fontWeight: '700', color: colors.primary }}>
            {item.invoice_number}
          </Text>
          <Text style={[Typography.h3(colors.text), { marginTop: 2 }]} numberOfLines={1}>
            {item.customer_name}
          </Text>
          <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
            Emisión: {item.issue_date} • Vence: {item.due_date}
          </Text>
        </View>
        {getStatusBadge(item.status)}
      </View>

      <View
        style={{
          marginTop: 12,
          paddingTop: 10,
          borderTopWidth: 1,
          borderTopColor: colors.border,
          flexDirection: 'row',
          justifyContent: 'space-between',
        }}
      >
        <View>
          <Text style={Typography.caption(colors.textMuted)}>Monto Total</Text>
          <Text style={{ fontSize: 16, fontWeight: '700', color: colors.text }}>
            ${item.total_amount.toFixed(2)}
          </Text>
        </View>

        <View>
          <Text style={Typography.caption(colors.textMuted)}>Amortizado</Text>
          <Text style={{ fontSize: 16, fontWeight: '600', color: colors.success }}>
            ${item.paid_amount.toFixed(2)}
          </Text>
        </View>

        <View>
          <Text style={Typography.caption(colors.textMuted)}>Saldo Pendiente</Text>
          <Text style={{ fontSize: 16, fontWeight: '700', color: item.balance > 0 ? colors.danger : colors.textMuted }}>
            ${item.balance.toFixed(2)}
          </Text>
        </View>
      </View>
    </Card>
  );

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      <Header
        title="Facturación B2B"
        subtitle="Control de cuentas por cobrar y pagos"
        onBack={() => router.back()}
      />

      {loading ? (
        <View style={{ padding: 18, gap: 12 }}>
          <Skeleton height={110} />
          <Skeleton height={110} />
          <Skeleton height={110} />
        </View>
      ) : (
        <FlatList
          data={invoices}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderInvoice}
          contentContainerStyle={{ padding: 18, paddingBottom: 30 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
        />
      )}
    </SafeAreaView>
  );
}
