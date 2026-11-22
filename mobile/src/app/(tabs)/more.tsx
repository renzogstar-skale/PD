/**
 * Prince Distributors B2B — Tab 5: Más (Modules Hub & Commercial Management)
 */

import React from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  SafeAreaView,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../../lib/theme';
import { Card, Typography } from '../../components/ui';

interface ModuleItem {
  id: string;
  title: string;
  subtitle: string;
  icon: string;
  route: string;
  badge?: string;
}

export default function MoreScreen() {
  const { colors, t, settings } = useTheme();
  const router = useRouter();

  const modules: ModuleItem[] = [
    {
      id: 'invoices',
      title: t('mod_invoices'),
      subtitle: 'Facturas emitidas, amortización de pagos y notas de crédito',
      icon: '🧾',
      route: '/invoices',
    },
    {
      id: 'sales_reps',
      title: t('mod_sales_reps'),
      subtitle: 'Cumplimiento de metas, comisiones y territorios de venta',
      icon: '🎯',
      route: '/sales-reps',
    },
    {
      id: 'profitability',
      title: t('mod_profitability'),
      subtitle: 'Análisis financiero: ventas, costos, utilidad y margen %',
      icon: '📈',
      route: '/profitability',
    },
    {
      id: 'intelligence',
      title: t('mod_intelligence'),
      subtitle: 'Clientes inactivos, alertas de recompra y riesgo comercial',
      icon: '🧠',
      route: '/intelligence',
      badge: 'Alertas',
    },
    {
      id: 'lifecycle',
      title: 'Ciclo de Vida del Producto',
      subtitle: '12 indicadores, etapa de cada producto, proyecciones y reposición',
      icon: '🔄',
      route: '/lifecycle',
      badge: 'PLM',
    },
    {
      id: 'settings',
      title: t('mod_settings'),
      subtitle: 'Roles, tema claro/oscuro, idioma y conexión con el servidor',
      icon: '⚙️',
      route: '/settings',
    },
  ];

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Header */}
      <View
        style={{
          paddingHorizontal: 20,
          paddingTop: 16,
          paddingBottom: 14,
          backgroundColor: colors.card,
          borderBottomWidth: 1,
          borderBottomColor: colors.border,
        }}
      >
        <Text style={Typography.h2(colors.text)}>☰ {t('more_title')}</Text>
        <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
          Gestión avanzada de Prince Distributors B2B Suite
        </Text>
      </View>

      <ScrollView contentContainerStyle={{ padding: 18, gap: 12 }}>
        {modules.map((mod) => (
          <Card
            key={mod.id}
            onPress={() => router.push(mod.route as any)}
            style={{ padding: 16 }}
          >
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <View
                style={{
                  width: 48,
                  height: 48,
                  borderRadius: 14,
                  backgroundColor: colors.primaryLight,
                  alignItems: 'center',
                  justifyContent: 'center',
                  marginRight: 14,
                }}
              >
                <Text style={{ fontSize: 24 }}>{mod.icon}</Text>
              </View>

              <View style={{ flex: 1 }}>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                  <Text style={Typography.h3(colors.text)}>{mod.title}</Text>
                  {mod.badge ? (
                    <View
                      style={{
                        backgroundColor: colors.danger,
                        paddingHorizontal: 6,
                        paddingVertical: 2,
                        borderRadius: 10,
                      }}
                    >
                      <Text style={{ color: '#FFFFFF', fontSize: 10, fontWeight: '700' }}>
                        {mod.badge}
                      </Text>
                    </View>
                  ) : null}
                </View>
                <Text style={[Typography.caption(colors.textMuted), { marginTop: 4 }]} numberOfLines={2}>
                  {mod.subtitle}
                </Text>
              </View>

              <Text style={{ fontSize: 20, color: colors.textMuted, marginLeft: 8 }}>›</Text>
            </View>
          </Card>
        ))}

        {/* Corporate Footer */}
        <View style={{ alignItems: 'center', marginTop: 30, marginBottom: 20 }}>
          <Text style={{ fontSize: 12, fontWeight: '700', color: colors.primary }}>
            PRINCE DISTRIBUTORS S.A.C.
          </Text>
          <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
            Versión Móvil 1.0.0 (Build B2B-2026)
          </Text>
          <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
            Rol: {settings.role.toUpperCase()} • Modo {settings.theme.toUpperCase()}
          </Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
