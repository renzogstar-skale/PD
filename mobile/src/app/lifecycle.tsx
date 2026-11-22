/**
 * Prince Distributors B2B — Ciclo de Vida del Producto e Inteligencia Comercial
 * Versión Móvil Nativa (Expo / React Native)
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  SafeAreaView,
  RefreshControl,
  TouchableOpacity,
  Alert,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../lib/theme';
import { Card, Badge, Button, Header, Typography, KpiInfo } from '../components/ui';
import { kpiShort } from '../lib/kpi';

interface KpiItem {
  kpi: string;
  value: string;
  diff: string;
  status: 'favorable' | 'advertencia' | 'critico';
  meta: string;
}

interface RecItem {
  id: number;
  sku: string;
  title: string;
  problem: string;
  action: string;
  impact: string;
  priority: 'urgent' | 'high' | 'medium';
  status: 'pending' | 'approved';
}

export default function ProductLifecycleScreen() {
  const { colors, settings } = useTheme();
  const router = useRouter();

  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedStage, setSelectedStage] = useState('ALL');

  // 12 KPIs principales
  const kpis: KpiItem[] = [
    { kpi: 'sell_through', value: '58.4%', diff: '+4.2% vs ant.', status: 'favorable', meta: 'Meta: ≥ 50%' },
    { kpi: 'turnover', value: '1.38x', diff: '+0.13x', status: 'favorable', meta: 'Meta: 1.25x' },
    { kpi: 'coverage_dio', value: '24.2 d', diff: '-4.3 d', status: 'favorable', meta: 'Rango: 20-30 d' },
    { kpi: 'gmroi', value: '$3.15', diff: '+$0.30', status: 'favorable', meta: 'Por cada $1 en inventario' },
    { kpi: 'gmros', value: '$415/m²', diff: '+$35/m²', status: 'favorable', meta: 'Capacidad 85m²' },
    { kpi: 'growth_skus', value: '21.4%', diff: '3 productos clave', status: 'favorable', meta: 'Demanda +15%' },
    { kpi: 'maturity_skus', value: '28.6%', diff: '4 productos estables', status: 'favorable', meta: 'Estabilidad 98%' },
    { kpi: 'decline_skus', value: '14.3%', diff: '2 productos en riesgo', status: 'advertencia', meta: 'Caída > -10%' },
    { kpi: 'immobilized_capital', value: '$17,215', diff: '-$2,260 ant.', status: 'advertencia', meta: '$9,375 liquidable' },
    { kpi: 'stockout_risk', value: '14.3%', diff: '-3.7%', status: 'advertencia', meta: '2 en punto de reposición' },
    { kpi: 'net_margin_post_discounts', value: '24.2%', diff: '+1.4%', status: 'favorable', meta: 'Desc. 3.8%' },
    { kpi: 'forecast_accuracy', value: '92.4%', diff: '+3.3%', status: 'favorable', meta: 'Método estadístico de tendencia' },
  ];

  // Recomendaciones comerciales prioritarias
  const [recommendations, setRecommendations] = useState<RecItem[]>([
    {
      id: 1,
      sku: 'SKU-CER-008',
      title: 'Reposición Preventiva: Avena Orgánica',
      problem: 'Ventas crecieron +24.8%. El inventario alcanza para 12.6 días y el proveedor tarda 14 días en reponer.',
      action: 'Emitir orden de compra de 1,800 unidades (150 cajas) a Quaker para no quedarse sin inventario en 11 días.',
      impact: '+$4,500 USD',
      priority: 'high',
      status: 'pending',
    },
    {
      id: 2,
      sku: 'SKU-QUE-021',
      title: 'Riesgo de quedarse sin inventario: Queso Mozzarella Barra',
      problem: '350 unidades en existencia y se venden 35 por día en hoteles y restaurantes.',
      action: 'Generar solicitud prioritaria de 600 uds y verificar lote refrigerado.',
      impact: '+$3,200 USD',
      priority: 'urgent',
      status: 'pending',
    },
    {
      id: 3,
      sku: 'SKU-GAL-030',
      title: 'Liquidación con rebaja de precio del 25%: Galleta Familiar',
      problem: 'Producto descontinuado con 2,100 unidades sin venta ($5,250 al costo).',
      action: 'Aprobar una rebaja de precio del 25% ($2.25 por unidad) para recuperar $4,200.',
      impact: '+$4,200 USD',
      priority: 'high',
      status: 'pending',
    },
  ]);

  const onRefresh = () => {
    setRefreshing(true);
    setTimeout(() => {
      setRefreshing(false);
    }, 600);
  };

  const approveRecommendation = (id: number) => {
    setRecommendations((prev) =>
      prev.map((r) => (r.id === id ? { ...r, status: 'approved' } : r))
    );
    Alert.alert(
      'Acción Comercial Aprobada',
      'La recomendación fue autorizada y enviada a la cola de abastecimiento/markdown.'
    );
  };

  const statusBorderColors = {
    favorable: '#10B981',
    advertencia: '#F59E0B',
    critico: '#EF4444',
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Header */}
      <Header
        title="Ciclo de Vida del Producto"
        subtitle="Analiza rendimiento y rentabilidad de portafolio"
        showBack
      />

      <ScrollView
        contentContainerStyle={{ padding: 16, gap: 16 }}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        {/* Distribución del Portafolio */}
        <Card style={{ padding: 14 }}>
          <Text style={[Typography.h3(colors.text), { marginBottom: 8 }]}>
            Distribución del Portafolio
          </Text>
          <View style={{ flexDirection: 'row', height: 10, borderRadius: 5, overflow: 'hidden' }}>
            <View style={{ flex: 14, backgroundColor: '#3B82F6' }} />
            <View style={{ flex: 21, backgroundColor: '#10B981' }} />
            <View style={{ flex: 29, backgroundColor: '#6366F1' }} />
            <View style={{ flex: 14, backgroundColor: '#F59E0B' }} />
            <View style={{ flex: 14, backgroundColor: '#EF4444' }} />
            <View style={{ flex: 8, backgroundColor: '#94A3B8' }} />
          </View>
          <View
            style={{
              flexDirection: 'row',
              flexWrap: 'wrap',
              gap: 8,
              marginTop: 10,
            }}
          >
            <Text style={{ fontSize: 10, color: '#3B82F6', fontWeight: '600' }}>● Intro: 14.3%</Text>
            <Text style={{ fontSize: 10, color: '#10B981', fontWeight: '600' }}>● Crecimiento: 21.4%</Text>
            <Text style={{ fontSize: 10, color: '#6366F1', fontWeight: '600' }}>● Madurez: 28.6%</Text>
            <Text style={{ fontSize: 10, color: '#F59E0B', fontWeight: '600' }}>● Declive: 14.3%</Text>
            <Text style={{ fontSize: 10, color: '#EF4444', fontWeight: '600' }}>● Salida: 14.3%</Text>
          </View>
        </Card>

        {/* Grid de los 12 KPIs */}
        <View>
          <Text style={[Typography.h3(colors.text), { marginBottom: 10 }]}>
            12 indicadores del ciclo de vida del producto
          </Text>
          <View
            style={{
              flexDirection: 'row',
              flexWrap: 'wrap',
              gap: 10,
              justifyContent: 'space-between',
            }}
          >
            {kpis.map((k, idx) => (
              <Card
                key={idx}
                style={{
                  width: '48%',
                  padding: 12,
                  borderLeftWidth: 4,
                  borderLeftColor: statusBorderColors[k.status],
                }}
              >
                <View style={{ flexDirection: 'row', alignItems: 'flex-start' }}>
                  <Text
                    style={[
                      Typography.caption(colors.textMuted),
                      { fontSize: 11, fontWeight: '600', flexShrink: 1 },
                    ]}
                    numberOfLines={2}
                  >
                    {kpiShort(k.kpi, settings.language === 'en' ? 'en' : 'es')}
                  </Text>
                  <KpiInfo kpi={k.kpi} />
                </View>
                <Text
                  style={[
                    Typography.h2(colors.text),
                    { fontSize: 18, marginTop: 4, fontWeight: '700' },
                  ]}
                >
                  {k.value}
                </Text>
                <Text
                  style={{
                    fontSize: 10,
                    fontWeight: '600',
                    color: k.status === 'favorable' ? '#10B981' : '#F59E0B',
                    marginTop: 2,
                  }}
                >
                  {k.diff}
                </Text>
                <Text
                  style={[
                    Typography.caption(colors.textMuted),
                    { fontSize: 9, marginTop: 4 },
                  ]}
                >
                  {k.meta}
                </Text>
              </Card>
            ))}
          </View>
        </View>

        {/* Recomendaciones Comerciales y Reposición */}
        <View>
          <Text style={[Typography.h3(colors.text), { marginBottom: 10 }]}>
            Recomendaciones Comerciales
          </Text>
          <View style={{ gap: 12 }}>
            {recommendations.map((r) => {
              const isApproved = r.status === 'approved';
              return (
                <Card
                  key={r.id}
                  style={{
                    padding: 14,
                    borderColor: isApproved ? '#10B981' : colors.border,
                    borderWidth: 1,
                  }}
                >
                  <View
                    style={{
                      flexDirection: 'row',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                    }}
                  >
                    <Badge
                      label={r.priority === 'urgent' ? 'URGENTE' : 'ALTA PRIORIDAD'}
                      variant={r.priority === 'urgent' ? 'danger' : 'warning'}
                    />
                    <Text
                      style={{
                        fontFamily: 'monospace',
                        fontWeight: 'bold',
                        color: '#10B981',
                        fontSize: 12,
                      }}
                    >
                      {r.impact}
                    </Text>
                  </View>

                  <Text
                    style={[
                      Typography.h3(colors.text),
                      { fontSize: 14, marginTop: 6, fontWeight: '700' },
                    ]}
                  >
                    {r.title}
                  </Text>
                  <Text
                    style={[
                      Typography.caption(colors.textMuted),
                      { fontFamily: 'monospace', fontSize: 10, marginTop: 1 },
                    ]}
                  >
                    {r.sku}
                  </Text>

                  <Text
                    style={[
                      Typography.body(colors.text),
                      { fontSize: 12, marginTop: 6, lineHeight: 16 },
                    ]}
                  >
                    <Text style={{ fontWeight: '600' }}>Problema: </Text>
                    {r.problem}
                  </Text>

                  <View
                    style={{
                      backgroundColor: colors.card,
                      padding: 8,
                      borderRadius: 8,
                      borderWidth: 1,
                      borderColor: colors.border,
                      marginTop: 6,
                    }}
                  >
                    <Text
                      style={{
                        fontSize: 11,
                        color: colors.primary,
                        lineHeight: 15,
                      }}
                    >
                      <Text style={{ fontWeight: 'bold' }}>Acción: </Text>
                      {r.action}
                    </Text>
                  </View>

                  <View style={{ marginTop: 10 }}>
                    {isApproved ? (
                      <View
                        style={{
                          backgroundColor: 'rgba(16, 185, 129, 0.1)',
                          padding: 8,
                          borderRadius: 8,
                          alignItems: 'center',
                        }}
                      >
                        <Text
                          style={{
                            color: '#10B981',
                            fontWeight: 'bold',
                            fontSize: 12,
                          }}
                        >
                          ✓ Aprobado por Dirección
                        </Text>
                      </View>
                    ) : (
                      <Button
                        title="Aprobar Acción Comercial"
                        onPress={() => approveRecommendation(r.id)}
                        variant="primary"
                        size="sm"
                      />
                    )}
                  </View>
                </Card>
              );
            })}
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
