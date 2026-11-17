/**
 * Prince Distributors B2B — Inteligencia Comercial (Alertas & Recompra)
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  SafeAreaView,
  RefreshControl,
  TouchableOpacity,
  Alert,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../lib/theme';
import { ApiClient } from '../lib/api';
import { Card, Badge, Button, Header, Skeleton, Typography } from '../components/ui';
import { CommercialAlert } from '../types';

export default function IntelligenceScreen() {
  const { colors } = useTheme();
  const router = useRouter();

  const [alerts, setAlerts] = useState<CommercialAlert[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const data = await ApiClient.getCommercialAlerts();
      setAlerts(data);
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

  const markAsReviewed = (id: number) => {
    setAlerts((prev) => prev.filter((a) => a.id !== id));
    Alert.alert('Alerta Revisada', 'La alerta fue marcada como gestionada.');
  };

  const renderAlert = ({ item }: { item: CommercialAlert }) => (
    <Card
      style={{
        marginBottom: 12,
        borderLeftWidth: 4,
        borderLeftColor:
          item.severity === 'critical'
            ? colors.danger
            : item.severity === 'high'
            ? colors.warning
            : colors.info,
      }}
    >
      <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
        <Text style={[Typography.h3(colors.text), { flex: 1, paddingRight: 8 }]}>
          {item.title}
        </Text>
        <Badge
          label={item.severity}
          variant={
            item.severity === 'critical'
              ? 'danger'
              : item.severity === 'high'
              ? 'warning'
              : 'info'
          }
        />
      </View>

      <Text style={[Typography.body(colors.textMuted), { marginTop: 6, fontSize: 13 }]}>
        {item.description}
      </Text>

      {item.customer_name ? (
        <Text style={[Typography.caption(colors.text), { marginTop: 4, fontWeight: '600' }]}>
          🏢 {item.customer_name}
        </Text>
      ) : null}

      <View
        style={{
          marginTop: 10,
          paddingTop: 10,
          borderTopWidth: 1,
          borderTopColor: colors.border,
          backgroundColor: colors.background,
          padding: 8,
          borderRadius: 8,
        }}
      >
        <Text style={{ fontSize: 12, fontWeight: '700', color: colors.primary }}>
          💡 Acción Recomendada:
        </Text>
        <Text style={{ fontSize: 12, color: colors.text, marginTop: 2 }}>
          {item.action_suggested}
        </Text>
      </View>

      <View style={{ flexDirection: 'row', justifyContent: 'flex-end', marginTop: 10, gap: 8 }}>
        <Button
          title="Marcar como Revisada"
          variant="outline"
          size="sm"
          onPress={() => markAsReviewed(item.id)}
        />
      </View>
    </Card>
  );

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      <Header
        title="Inteligencia Comercial"
        subtitle="Reglas automáticas, recompra y mitigación de riesgo"
        onBack={() => router.back()}
      />

      {loading ? (
        <View style={{ padding: 18, gap: 12 }}>
          <Skeleton height={140} />
          <Skeleton height={140} />
        </View>
      ) : (
        <FlatList
          data={alerts}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderAlert}
          contentContainerStyle={{ padding: 18, paddingBottom: 30 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
          ListEmptyComponent={
            <View style={{ alignItems: 'center', marginTop: 40, padding: 20 }}>
              <Text style={{ fontSize: 40, marginBottom: 12 }}>🎉</Text>
              <Text style={Typography.h3(colors.text)}>Sin alertas comerciales pendientes</Text>
              <Text style={[Typography.caption(colors.textMuted), { textAlign: 'center', marginTop: 4 }]}>
                Todas las alertas de inactividad, cartera vencida y metas han sido atendidas.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}
