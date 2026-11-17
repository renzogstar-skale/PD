/**
 * Prince Distributors B2B — Fuerza de Ventas, Metas & Productividad
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
import { Card, Header, Skeleton, Typography } from '../components/ui';
import { SalesRep } from '../types';

export default function SalesRepsScreen() {
  const { colors } = useTheme();
  const router = useRouter();

  const [reps, setReps] = useState<SalesRep[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const data = await ApiClient.getSalesReps();
      setReps(data);
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

  const renderRep = ({ item }: { item: SalesRep }) => {
    const isTargetMet = item.target_achievement_pct >= 80;

    return (
      <Card style={{ marginBottom: 12 }}>
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
          <View>
            <Text style={Typography.h3(colors.text)}>{item.name}</Text>
            <Text style={Typography.caption(colors.textMuted)}>
              {item.code} • Zona: {item.territory}
            </Text>
          </View>
          <View
            style={{
              backgroundColor: isTargetMet ? colors.primaryLight : colors.border,
              paddingHorizontal: 10,
              paddingVertical: 4,
              borderRadius: 12,
            }}
          >
            <Text
              style={{
                fontSize: 13,
                fontWeight: '700',
                color: isTargetMet ? colors.primary : colors.textMuted,
              }}
            >
              {item.target_achievement_pct.toFixed(1)}% Meta
            </Text>
          </View>
        </View>

        {/* Progress bar */}
        <View style={{ marginTop: 12 }}>
          <View
            style={{
              height: 6,
              backgroundColor: colors.border,
              borderRadius: 3,
              overflow: 'hidden',
            }}
          >
            <View
              style={{
                height: '100%',
                width: `${Math.min(100, item.target_achievement_pct)}%`,
                backgroundColor: isTargetMet ? colors.primary : colors.warning,
              }}
            />
          </View>
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
            <Text style={Typography.caption(colors.textMuted)}>Venta Actual</Text>
            <Text style={{ fontSize: 15, fontWeight: '700', color: colors.text }}>
              ${item.current_sales.toLocaleString()}
            </Text>
          </View>
          <View>
            <Text style={Typography.caption(colors.textMuted)}>Cuota Mes</Text>
            <Text style={{ fontSize: 15, fontWeight: '600', color: colors.textMuted }}>
              ${item.monthly_target.toLocaleString()}
            </Text>
          </View>
          <View>
            <Text style={Typography.caption(colors.textMuted)}>Comisión Est.</Text>
            <Text style={{ fontSize: 15, fontWeight: '700', color: colors.primary }}>
              ${item.commission_earned.toLocaleString()}
            </Text>
          </View>
        </View>
      </Card>
    );
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      <Header
        title="Fuerza de Ventas"
        subtitle="Cumplimiento de cuotas comerciales y territorios"
        onBack={() => router.back()}
      />

      {loading ? (
        <View style={{ padding: 18, gap: 12 }}>
          <Skeleton height={120} />
          <Skeleton height={120} />
        </View>
      ) : (
        <FlatList
          data={reps}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderRep}
          contentContainerStyle={{ padding: 18, paddingBottom: 30 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
        />
      )}
    </SafeAreaView>
  );
}
