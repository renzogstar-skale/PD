/**
 * Prince Distributors B2B — Tab 1: Inicio (Executive Commercial Dashboard)
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  SafeAreaView,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../../lib/theme';
import { ApiClient } from '../../lib/api';
import { Card, Button, Badge, StatCard, Typography } from '../../components/ui';
import { SalesOverviewKPIs, CommercialAlert } from '../../types';

export default function HomeScreen() {
  const { colors, t, settings } = useTheme();
  const router = useRouter();

  const [kpis, setKpis] = useState<SalesOverviewKPIs | null>(null);
  const [alerts, setAlerts] = useState<CommercialAlert[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = useCallback(async () => {
    try {
      const [overviewData, alertsData] = await Promise.all([
        ApiClient.getSalesOverview(),
        ApiClient.getCommercialAlerts(),
      ]);
      setKpis(overviewData);
      setAlerts(alertsData);
    } catch {
      // Handled inside ApiClient with fallbacks
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const roleLabels: Record<string, string> = {
    sales_rep: 'Vendedor',
    supervisor: 'Supervisor',
    admin: 'Administrador',
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Top App Header */}
      <View
        style={{
          paddingHorizontal: 20,
          paddingTop: 16,
          paddingBottom: 12,
          backgroundColor: colors.card,
          borderBottomWidth: 1,
          borderBottomColor: colors.border,
          flexDirection: 'row',
          justifyContent: 'space-between',
          alignItems: 'center',
        }}
      >
        <View>
          <Text style={{ fontSize: 12, fontWeight: '700', color: colors.primary, letterSpacing: 1 }}>
            PRINCE DISTRIBUTORS
          </Text>
          <Text style={Typography.h2(colors.text)}>Panel B2B</Text>
        </View>

        <TouchableOpacity
          onPress={() => router.push('/settings' as any)}
          style={{
            flexDirection: 'row',
            alignItems: 'center',
            backgroundColor: colors.primaryLight,
            paddingHorizontal: 12,
            paddingVertical: 6,
            borderRadius: 20,
          }}
        >
          <Text style={{ fontSize: 12, fontWeight: '600', color: colors.primary }}>
            👤 {roleLabels[settings.role] || 'Comercial'}
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        contentContainerStyle={{ padding: 18, paddingBottom: 40 }}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
      >
        {/* Priority Action: Nuevo Pedido (One-handed prominent target) */}
        <Card
          style={{
            backgroundColor: colors.primary,
            borderColor: colors.primaryDark,
            padding: 18,
            marginBottom: 20,
          }}
        >
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
            <View style={{ flex: 1, paddingRight: 10 }}>
              <Text style={{ color: '#FFFFFF', fontSize: 18, fontWeight: '700' }}>
                🚀 {t('btn_new_order')}
              </Text>
              <Text style={{ color: '#E8F8F0', fontSize: 13, marginTop: 4 }}>
                Cotización rápida en 3 pasos con validación de crédito y tarifas especiales.
              </Text>
            </View>
            <Button
              title="Iniciar"
              variant="secondary"
              size="md"
              onPress={() => router.push('/new-order' as any)}
            />
          </View>
        </Card>

        {/* Commercial KPIs 2x2 Grid */}
        <Text style={[Typography.h3(colors.text), { marginBottom: 12 }]}>
          📈 Resumen de Operaciones
        </Text>

        <View style={{ flexDirection: 'row', gap: 12, marginBottom: 12 }}>
          <StatCard
            title={t('kpi_today_sales')}
            value={`$${(kpis?.today_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}`}
            icon="💵"
            subtitle="Hoy vs ayer +12%"
            color={colors.primary}
          />
          <StatCard
            title={t('kpi_orders')}
            value={String(kpis?.today_orders_count || 0)}
            icon="📦"
            subtitle="100% procesados"
            color={colors.info}
          />
        </View>

        <View style={{ flexDirection: 'row', gap: 12, marginBottom: 24 }}>
          <StatCard
            title={t('kpi_active_customers')}
            value={String(kpis?.active_customers_count || 0)}
            icon="👥"
            subtitle="Cartera con compra"
            color={colors.warning}
          />
          <StatCard
            title={t('kpi_cxc')}
            value={`$${(kpis?.total_cxc || 0).toLocaleString('en-US', { minimumFractionDigits: 0 })}`}
            icon="💳"
            subtitle="Por cobrar < 30 días"
            color={colors.danger}
          />
        </View>

        {/* Alerts & Commercial Intelligence preview */}
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
          <Text style={Typography.h3(colors.text)}>
            🔔 {t('alerts_title')}
          </Text>
          <TouchableOpacity onPress={() => router.push('/intelligence' as any)}>
            <Text style={{ fontSize: 13, color: colors.primary, fontWeight: '600' }}>
              Ver todas ({alerts.length}) ›
            </Text>
          </TouchableOpacity>
        </View>

        {alerts.length === 0 ? (
          <Card style={{ padding: 20, alignItems: 'center' }}>
            <Text style={Typography.body(colors.textMuted)}>{t('no_alerts')}</Text>
          </Card>
        ) : (
          alerts.slice(0, 3).map((alert) => (
            <Card
              key={alert.id}
              style={{ marginBottom: 10, borderLeftWidth: 4, borderLeftColor: alert.severity === 'critical' ? colors.danger : colors.warning }}
              onPress={() => router.push('/intelligence' as any)}
            >
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <Text style={[Typography.h3(colors.text), { flex: 1, fontSize: 15 }]}>
                  {alert.title}
                </Text>
                <Badge
                  label={alert.severity}
                  variant={alert.severity === 'critical' ? 'danger' : 'warning'}
                />
              </View>

              <Text style={[Typography.body(colors.textMuted), { marginTop: 6, fontSize: 13 }]}>
                {alert.description}
              </Text>

              <View
                style={{
                  marginTop: 10,
                  paddingTop: 8,
                  borderTopWidth: 1,
                  borderTopColor: colors.border,
                  flexDirection: 'row',
                  alignItems: 'center',
                }}
              >
                <Text style={{ fontSize: 12, fontWeight: '600', color: colors.primary }}>
                  💡 Sugerencia:
                </Text>
                <Text style={{ fontSize: 12, color: colors.text, marginLeft: 6, flex: 1 }} numberOfLines={1}>
                  {alert.action_suggested}
                </Text>
              </View>
            </Card>
          ))
        )}

        {/* Quick Shortcuts */}
        <Text style={[Typography.h3(colors.text), { marginTop: 14, marginBottom: 12 }]}>
          ⚡ Accesos Rápidos
        </Text>

        <View style={{ flexDirection: 'row', gap: 10 }}>
          <TouchableOpacity
            onPress={() => router.push('/customers' as any)}
            style={{
              flex: 1,
              backgroundColor: colors.card,
              padding: 14,
              borderRadius: 14,
              borderWidth: 1,
              borderColor: colors.border,
              alignItems: 'center',
            }}
          >
            <Text style={{ fontSize: 24, marginBottom: 4 }}>👥</Text>
            <Text style={Typography.caption(colors.text)}>Clientes</Text>
          </TouchableOpacity>

          <TouchableOpacity
            onPress={() => router.push('/products' as any)}
            style={{
              flex: 1,
              backgroundColor: colors.card,
              padding: 14,
              borderRadius: 14,
              borderWidth: 1,
              borderColor: colors.border,
              alignItems: 'center',
            }}
          >
            <Text style={{ fontSize: 24, marginBottom: 4 }}>🏷️</Text>
            <Text style={Typography.caption(colors.text)}>Catálogo</Text>
          </TouchableOpacity>

          <TouchableOpacity
            onPress={() => router.push('/invoices' as any)}
            style={{
              flex: 1,
              backgroundColor: colors.card,
              padding: 14,
              borderRadius: 14,
              borderWidth: 1,
              borderColor: colors.border,
              alignItems: 'center',
            }}
          >
            <Text style={{ fontSize: 24, marginBottom: 4 }}>🧾</Text>
            <Text style={Typography.caption(colors.text)}>Facturas</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
