/**
 * Prince Distributors B2B — Configuración, Roles, Idioma & Caché
 */

import React, { useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  SafeAreaView,
  TouchableOpacity,
  Alert,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../lib/theme';
import { Storage } from '../lib/storage';
import { Card, Button, Header, Typography } from '../components/ui';
import { UserRole } from '../types';

export default function SettingsScreen() {
  const { colors, settings, updateSettings, t } = useTheme();
  const router = useRouter();

  const [clearing, setClearing] = useState(false);

  const roles: Array<{ role: UserRole; title: string; desc: string }> = [
    {
      role: 'sales_rep',
      title: 'Vendedor Comercial',
      desc: 'Cartera asignada, pedidos, cotizaciones y catálogo',
    },
    {
      role: 'supervisor',
      title: 'Supervisor de Ventas',
      desc: 'Supervisión de equipo, aprobación de créditos y metas',
    },
    {
      role: 'admin',
      title: 'Administrador Global',
      desc: 'Acceso total a finanzas, costos, seguridad y configuración',
    },
    {
      role: 'merchandiser',
      title: 'Reponedor',
      desc: 'Mi Jornada, control de turnos, escáner UPC, reposición y mermas',
    },
  ];

  const handleClearCache = async () => {
    setClearing(true);
    try {
      await Storage.clearCache();
      Alert.alert('Caché Limpiado', 'Los datos locales han sido restablecidos.');
    } finally {
      setClearing(false);
    }
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      <Header
        title={t('settings_title')}
        subtitle="Preferencias del usuario, rol y conectividad"
        onBack={() => router.back()}
      />

      <ScrollView contentContainerStyle={{ padding: 18, gap: 14 }}>
        {/* Role Selector */}
        <Card>
          <Text style={Typography.h3(colors.text)}>{t('setting_role')}</Text>
          <Text style={[Typography.caption(colors.textMuted), { marginBottom: 12 }]}>
            Cambie su perfil activo para verificar permisos según el rol
          </Text>

          {roles.map((r) => {
            const isSelected = settings.role === r.role;
            return (
              <TouchableOpacity
                key={r.role}
                onPress={() => updateSettings({ role: r.role })}
                style={{
                  padding: 12,
                  borderRadius: 10,
                  backgroundColor: isSelected ? colors.primaryLight : colors.background,
                  borderWidth: 1.5,
                  borderColor: isSelected ? colors.primary : colors.border,
                  marginBottom: 8,
                }}
              >
                <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                  <Text
                    style={{
                      fontSize: 14,
                      fontWeight: '700',
                      color: isSelected ? colors.primary : colors.text,
                    }}
                  >
                    {r.title}
                  </Text>
                  {isSelected ? (
                    <Text style={{ fontSize: 16, color: colors.primary }}>✔</Text>
                  ) : null}
                </View>
                <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
                  {r.desc}
                </Text>
              </TouchableOpacity>
            );
          })}
        </Card>

        {/* Visual Theme */}
        <Card>
          <Text style={Typography.h3(colors.text)}>{t('setting_theme')}</Text>
          <View style={{ flexDirection: 'row', gap: 10, marginTop: 10 }}>
            {(['light', 'dark', 'system'] as const).map((th) => {
              const isSelected = settings.theme === th;
              const labels = { light: '☀️ Claro', dark: '🌙 Oscuro', system: '📱 Sistema' };
              return (
                <TouchableOpacity
                  key={th}
                  onPress={() => updateSettings({ theme: th })}
                  style={{
                    flex: 1,
                    padding: 12,
                    borderRadius: 10,
                    backgroundColor: isSelected ? colors.primary : colors.background,
                    borderWidth: 1,
                    borderColor: isSelected ? colors.primary : colors.border,
                    alignItems: 'center',
                  }}
                >
                  <Text
                    style={{
                      fontSize: 13,
                      fontWeight: '600',
                      color: isSelected ? '#FFFFFF' : colors.text,
                    }}
                  >
                    {labels[th]}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        </Card>

        {/* Language */}
        <Card>
          <Text style={Typography.h3(colors.text)}>{t('setting_lang')}</Text>
          <View style={{ flexDirection: 'row', gap: 10, marginTop: 10 }}>
            {(['es', 'en'] as const).map((lang) => {
              const isSelected = settings.language === lang;
              const labels = { es: '🇪🇸 Español', en: '🇺🇸 English' };
              return (
                <TouchableOpacity
                  key={lang}
                  onPress={() => updateSettings({ language: lang })}
                  style={{
                    flex: 1,
                    padding: 12,
                    borderRadius: 10,
                    backgroundColor: isSelected ? colors.primary : colors.background,
                    borderWidth: 1,
                    borderColor: isSelected ? colors.primary : colors.border,
                    alignItems: 'center',
                  }}
                >
                  <Text
                    style={{
                      fontSize: 13,
                      fontWeight: '600',
                      color: isSelected ? '#FFFFFF' : colors.text,
                    }}
                  >
                    {labels[lang]}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        </Card>

        {/* API Server */}
        <Card>
          <Text style={Typography.h3(colors.text)}>{t('setting_server')}</Text>
          <Text style={[Typography.caption(colors.textMuted), { marginBottom: 10 }]}>
            Servidor desde el que la app obtiene los datos de Prince Distributors
          </Text>

          {[
            { label: '🌐 Vercel Producción', url: 'https://pd-khaki-eight.vercel.app/api' },
            { label: '💻 Servidor Local (Dev)', url: 'http://127.0.0.1:8080/api' },
          ].map((srv) => {
            const isSelected = settings.apiBaseUrl === srv.url;
            return (
              <TouchableOpacity
                key={srv.url}
                onPress={() => updateSettings({ apiBaseUrl: srv.url })}
                style={{
                  padding: 10,
                  borderRadius: 8,
                  backgroundColor: isSelected ? colors.primaryLight : colors.background,
                  borderWidth: 1,
                  borderColor: isSelected ? colors.primary : colors.border,
                  marginBottom: 6,
                }}
              >
                <Text
                  style={{
                    fontSize: 13,
                    fontWeight: '700',
                    color: isSelected ? colors.primary : colors.text,
                  }}
                >
                  {srv.label}
                </Text>
                <Text style={{ fontSize: 11, color: colors.textMuted }}>{srv.url}</Text>
              </TouchableOpacity>
            );
          })}
        </Card>

        {/* Local Cache Management */}
        <Card>
          <Text style={Typography.h3(colors.text)}>Persistencia & Caché</Text>
          <Text style={[Typography.caption(colors.textMuted), { marginBottom: 12 }]}>
            Eliminar datos temporales de clientes y catálogo almacenados en el dispositivo.
          </Text>
          <Button
            title={t('clear_cache')}
            variant="outline"
            loading={clearing}
            onPress={handleClearCache}
          />
        </Card>

        {/* USA Operations & Branch Profile */}
        <Card style={{ backgroundColor: colors.card, borderColor: colors.primary, borderWidth: 1 }}>
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8 }}>
            <Text style={{ fontSize: 18 }}>🇺🇸</Text>
            <View>
              <Text style={{ fontSize: 13, fontWeight: '700', color: colors.text }}>Prince Distributors — Hallandale HQ</Text>
              <Text style={{ fontSize: 11, color: colors.textMuted }}>Sede Central Operativa USA</Text>
            </View>
          </View>
          <View style={{ paddingVertical: 6, borderTopWidth: 1, borderTopColor: colors.border, gap: 4 }}>
            <Text style={{ fontSize: 12, color: colors.textMuted }}>📍 Ubicación: <Text style={{ color: colors.text, fontWeight: '600' }}>Hallandale Beach, FL 33009</Text></Text>
            <Text style={{ fontSize: 12, color: colors.textMuted }}>💵 Moneda: <Text style={{ color: colors.text, fontWeight: '600' }}>USD ($) • Formato en-US</Text></Text>
            <Text style={{ fontSize: 12, color: colors.textMuted }}>🕒 Zona Horaria: <Text style={{ color: colors.text, fontWeight: '600' }}>America/New_York (EST)</Text></Text>
          </View>
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}
