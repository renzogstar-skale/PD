/**
 * Prince Distributors B2B — Rentabilidad y Márgenes Comerciales
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  SafeAreaView,
  RefreshControl,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../lib/theme';
import { ApiClient } from '../lib/api';
import { Card, Header, StatCard, Skeleton, Typography } from '../components/ui';
import { ProfitabilityMetrics } from '../types';

export default function ProfitabilityScreen() {
  const { colors } = useTheme();
  const router = useRouter();

  const [metrics, setMetrics] = useState<ProfitabilityMetrics | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const data = await ApiClient.getProfitability();
      setMetrics(data);
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

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      <Header
        title="Rentabilidad Comercial"
        subtitle="Ingresos, costos de mercadería y utilidad bruta"
        onBack={() => router.back()}
      />

      <ScrollView
        contentContainerStyle={{ padding: 18, gap: 14 }}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
      >
        {loading ? (
          <View style={{ gap: 12 }}>
            <Skeleton height={100} />
            <Skeleton height={100} />
            <Skeleton height={140} />
          </View>
        ) : (
          <>
            {/* Top KPIs */}
            <View style={{ flexDirection: 'row', gap: 12 }}>
              <StatCard
                title="Ventas Netas"
                value={`$${(metrics?.total_sales || 0).toLocaleString()}`}
                icon="💰"
                color={colors.primary}
              />
              <StatCard
                title="Costo de Ventas (COGS)"
                value={`$${(metrics?.total_cost || 0).toLocaleString()}`}
                icon="📦"
                color={colors.textMuted}
              />
            </View>

            <View style={{ flexDirection: 'row', gap: 12 }}>
              <StatCard
                title="Utilidad Bruta"
                value={`$${(metrics?.gross_profit || 0).toLocaleString()}`}
                icon="💵"
                color={colors.success}
              />
              <StatCard
                title="Margen Bruto"
                value={`${(metrics?.gross_margin_pct || 0).toFixed(1)}%`}
                icon="📊"
                color={colors.primary}
              />
            </View>

            {/* Category Analysis Card */}
            <Card style={{ marginTop: 6 }}>
              <Text style={Typography.h3(colors.text)}>Top Desempeño por Categoría</Text>
              <Text style={[Typography.caption(colors.textMuted), { marginTop: 2, marginBottom: 12 }]}>
                Líneas comerciales con mayor margen de contribución
              </Text>

              <View
                style={{
                  backgroundColor: colors.background,
                  padding: 12,
                  borderRadius: 10,
                  marginBottom: 8,
                }}
              >
                <Text style={Typography.caption(colors.textMuted)}>Categoría Líder:</Text>
                <Text style={{ fontSize: 16, fontWeight: '700', color: colors.text, marginTop: 2 }}>
                  🏆 {metrics?.top_profitable_category}
                </Text>
              </View>

              <View
                style={{
                  backgroundColor: colors.background,
                  padding: 12,
                  borderRadius: 10,
                }}
              >
                <Text style={Typography.caption(colors.textMuted)}>Producto Estrella:</Text>
                <Text style={{ fontSize: 16, fontWeight: '700', color: colors.primary, marginTop: 2 }}>
                  ⭐ {metrics?.top_profitable_product}
                </Text>
              </View>
            </Card>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}
