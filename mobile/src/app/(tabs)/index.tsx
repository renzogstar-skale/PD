/**
 * Prince Distributors B2B — Tab 1: Inicio
 * Adaptive Multirole Dashboard:
 * - Vendedor Comercial (Sales Rep)
 * - Supervisor de Ventas (Sales Supervisor)
 * - Administrador Global (Global Admin)
 * - Reponedor / Merchandiser ("MI JORNADA" Flow with UPC Camera Scanner & FEFO Restock)
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  SafeAreaView,
  Modal,
  TextInput,
  Alert,
} from 'react-native';
import { useRouter } from 'expo-router';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { useTheme } from '../../lib/theme';
import { ApiClient } from '../../lib/api';
import { Card, Button, Badge, StatCard, Typography } from '../../components/ui';
import {
  SalesOverviewKPIs,
  CommercialAlert,
  Task,
  InventoryItem,
  VisitSession,
  UserRole
} from '../../types';

export default function HomeScreen() {
  const { colors, t, settings, updateSettings } = useTheme();
  const router = useRouter();

  // Commercial state
  const [kpis, setKpis] = useState<SalesOverviewKPIs | null>(null);
  const [alerts, setAlerts] = useState<CommercialAlert[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Merchandiser / "MI JORNADA" state
  const [tasks, setTasks] = useState<Task[]>([]);
  const [inventory, setInventory] = useState<InventoryItem[]>([]);
  const [activeSession, setActiveSession] = useState<VisitSession | null>(null);
  const [visitActive, setVisitActive] = useState(true);
  const [scheduleModalVisible, setScheduleModalVisible] = useState(false);
  const [employeeShifts, setEmployeeShifts] = useState<any[]>([]);

  // UPC Scanner modal state
  const [scannerVisible, setScannerVisible] = useState(false);
  const [permission, requestPermission] = useCameraPermissions();
  const [manualUpcInput, setManualUpcInput] = useState('');
  const [scannedProduct, setScannedProduct] = useState<InventoryItem | null>(null);
  const [scannerMode, setScannerMode] = useState<'camera' | 'manual'>('camera');
  const [scanMessage, setScanMessage] = useState('');

  // Role Switcher Modal
  const [roleModalVisible, setRoleModalVisible] = useState(false);

  const loadData = useCallback(async () => {
    try {
      const [overviewData, alertsData, tasksData, invData, sessionData, shiftsData] = await Promise.all([
        ApiClient.getSalesOverview(),
        ApiClient.getCommercialAlerts(),
        ApiClient.getTasks(),
        ApiClient.getInventory(),
        ApiClient.getActiveSession(2),
        ApiClient.getEmployeeShifts(2),
      ]);
      setKpis(overviewData);
      setAlerts(alertsData);
      setTasks(tasksData);
      setInventory(invData);
      setActiveSession(sessionData);
      setEmployeeShifts(shiftsData);
    } catch {
      // Handled inside ApiClient
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadData();
    const unsubscribe = ApiClient.subscribeToChanges(
      () => loadData(),
      () => loadData(),
      () => loadData()
    );
    return () => {
      unsubscribe();
    };
  }, [loadData]);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  // Clock In / Clock Out Handlers
  const handleClockIn = async () => {
    try {
      const session = await ApiClient.clockIn(2, 1);
      setActiveSession(session);
      Alert.alert('Turno Iniciado', 'Marcación de entrada registrada en Prince Distributors — Hallandale HQ a las ' + session.clock_in);
    } catch (e: any) {
      Alert.alert('Error', e.message || 'No se pudo iniciar turno');
    }
  };

  const handleClockOut = async () => {
    try {
      await ApiClient.clockOut(2, 'Fin de jornada regular');
      setActiveSession(null);
      Alert.alert('Turno Finalizado', 'Marcación de salida registrada exitosamente. ¡Excelente labor en Hallandale Beach!');
    } catch (e: any) {
      Alert.alert('Error', e.message || 'No se pudo finalizar turno');
    }
  };

  // Task Completion Toggle
  const handleToggleTask = async (task: Task) => {
    const nextStatus = task.status === 'completed' ? 'in_progress' : 'completed';
    try {
      const updated = await ApiClient.updateTaskStatus(task.id, nextStatus, 'Marcado desde la aplicación móvil');
      setTasks(prev => prev.map(t => t.id === task.id ? { ...t, status: nextStatus } : t));
    } catch (e: any) {
      Alert.alert('Error', 'No se pudo actualizar la tarea');
    }
  };

  // UPC Barcode Lookup
  const handleLookupUpc = async (code: string) => {
    const clean = code.trim();
    if (!clean) return;
    const prod = await ApiClient.findProductByUpc(clean);
    if (prod) {
      setScannedProduct(prod);
      setScanMessage(`✓ Código UPC ${clean} localizado en inventario.`);
    } else {
      setScanMessage(`⚠️ Producto con UPC ${clean} no encontrado en catálogo.`);
    }
  };

  // Quick Restock from Warehouse to Shelf (Traslado Interno Bodega -> Góndola)
  const handleRestock = async (productId: number, qty: number = 10) => {
    const res = await ApiClient.restockShelf(productId, qty);
    if (res.success && res.product) {
      setScannedProduct(res.product);
      setInventory(prev => prev.map(p => p.id === productId ? res.product! : p));
      Alert.alert('Reposición Exitosa', `Se trasladaron ${qty} unidades de bodega a góndola (Lote ${res.product.lot}).`);
    } else {
      Alert.alert('Aviso', res.error || 'Stock insuficiente');
    }
  };

  // Record Shrinkage (Merma)
  const handleShrinkage = async (productId: number) => {
    const res = await ApiClient.recordShrinkage(productId, 1, 'Merma por embalaje averiado en góndola');
    if (res.success && res.product) {
      setScannedProduct(res.product);
      setInventory(prev => prev.map(p => p.id === productId ? res.product! : p));
      Alert.alert('Merma Registrada', `Se descontó 1 unidad de góndola de ${res.product.name}.`);
    }
  };

  const roleLabels: Record<UserRole, { label: string; icon: string }> = {
    sales_rep: { label: 'Vendedor Comercial', icon: '💼' },
    supervisor: { label: 'Supervisor de Ventas', icon: '👔' },
    admin: { label: 'Administrador Global', icon: '🏢' },
    merchandiser: { label: 'Reponedor / Merchandiser', icon: '🛒' },
  };

  const quickUpcs = [
    { code: '012000000133', name: 'Pepsi 12pk' },
    { code: '049000000443', name: 'Coca-Cola 20oz' },
    { code: '028400064088', name: 'Doritos 9.25oz' },
    { code: '070847811169', name: 'Monster 16oz' },
    { code: '041789001429', name: 'Goya Frijoles' },
    { code: '775015100102', name: 'Inca Kola 2L' },
  ];

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Top App Header with Quick Role Switcher Pill */}
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
          <Text style={{ fontSize: 11, fontWeight: '800', color: colors.primary, letterSpacing: 1 }}>
            PRINCE DISTRIBUTORS • USA
          </Text>
          <Text style={Typography.h2(colors.text)}>
            {settings.role === 'merchandiser' ? 'Mi Jornada Operativa' : 'Panel B2B'}
          </Text>
        </View>

        {/* Quick Role Switcher Button */}
        <TouchableOpacity
          onPress={() => setRoleModalVisible(true)}
          style={{
            flexDirection: 'row',
            alignItems: 'center',
            backgroundColor: colors.primaryLight,
            paddingHorizontal: 12,
            paddingVertical: 6,
            borderRadius: 20,
            borderWidth: 1,
            borderColor: colors.primary,
          }}
        >
          <Text style={{ fontSize: 12, fontWeight: '700', color: colors.primary }}>
            {roleLabels[settings.role]?.icon} {roleLabels[settings.role]?.label.split(' ')[0]} ▾
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        contentContainerStyle={{ padding: 18, paddingBottom: 40 }}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
      >
        {/* =========================================================================
            ROLE VIEW 1: REPONEDOR / MERCHANDISER — "MI JORNADA"
           ========================================================================= */}
        {settings.role === 'merchandiser' && (
          <View style={{ gap: 16 }}>
            {/* Status Card: Shift & Assigned Store */}
            <Card style={{ backgroundColor: activeSession ? '#E8F8F0' : colors.card, borderColor: activeSession ? colors.primary : colors.border }}>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                <View>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                    <View style={{ width: 10, height: 10, borderRadius: 5, backgroundColor: activeSession ? colors.primary : colors.textMuted }} />
                    <Text style={{ fontSize: 12, fontWeight: '700', color: activeSession ? colors.primaryDark : colors.textMuted, textTransform: 'uppercase' }}>
                      {activeSession ? 'Turno en curso' : 'Turno No Iniciado'}
                    </Text>
                  </View>
                  <Text style={[Typography.h3(colors.text), { marginTop: 4 }]}>
                    Prince Distributors — Hallandale HQ
                  </Text>
                  <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
                    1250 E Hallandale Beach Blvd • Broward South
                  </Text>
                </View>

                <Badge
                  label={activeSession ? `Desde ${activeSession.clock_in}` : '8.0h Prog.'}
                  variant={activeSession ? 'success' : 'neutral'}
                />
              </View>

              {/* Action Buttons: Clock-In / Clock-Out */}
              <View style={{ flexDirection: 'row', gap: 10, marginTop: 14 }}>
                {!activeSession ? (
                  <Button
                    title="🟢 Iniciar Turno (Clock In)"
                    variant="primary"
                    size="md"
                    style={{ flex: 1 }}
                    onPress={handleClockIn}
                  />
                ) : (
                  <>
                    <Button
                      title={visitActive ? 'Visita en Curso' : 'Iniciar Visita'}
                      variant={visitActive ? 'secondary' : 'primary'}
                      size="md"
                      style={{ flex: 1 }}
                      onPress={() => {
                        setVisitActive(!visitActive);
                        Alert.alert('Visita en Tienda', visitActive ? 'Visita completada.' : 'Visita iniciada en góndolas de Hallandale Beach.');
                      }}
                    />
                    <Button
                      title="🔴 Clock Out"
                      variant="outline"
                      size="md"
                      style={{ flex: 1 }}
                      onPress={handleClockOut}
                    />
                  </>
                )}
              </View>
            </Card>

            {/* Quick Metrics Bar */}
            <View style={{ flexDirection: 'row', gap: 10 }}>
              <View style={{ flex: 1, backgroundColor: colors.card, padding: 12, borderRadius: 12, borderWidth: 1, borderColor: colors.border, alignItems: 'center' }}>
                <Text style={{ fontSize: 20 }}>📦</Text>
                <Text style={{ fontSize: 16, fontWeight: '800', color: colors.text, marginTop: 2 }}>
                  {activeSession?.restocked_units || 45}
                </Text>
                <Text style={{ fontSize: 11, color: colors.textMuted }}>Repuestas</Text>
              </View>

              <View style={{ flex: 1, backgroundColor: colors.card, padding: 12, borderRadius: 12, borderWidth: 1, borderColor: colors.border, alignItems: 'center' }}>
                <Text style={{ fontSize: 20 }}>✅</Text>
                <Text style={{ fontSize: 16, fontWeight: '800', color: colors.text, marginTop: 2 }}>
                  {tasks.filter(t => t.status === 'completed').length} / {tasks.length}
                </Text>
                <Text style={{ fontSize: 11, color: colors.textMuted }}>Tareas</Text>
              </View>

              <TouchableOpacity
                onPress={() => setScheduleModalVisible(true)}
                style={{ flex: 1, backgroundColor: colors.card, padding: 12, borderRadius: 12, borderWidth: 1, borderColor: colors.primary, alignItems: 'center' }}
              >
                <Text style={{ fontSize: 20 }}>🕒</Text>
                <Text style={{ fontSize: 16, fontWeight: '800', color: colors.primary, marginTop: 2 }}>
                  8.0 hrs
                </Text>
                <Text style={{ fontSize: 11, color: colors.primary, fontWeight: '600' }}>Ver Horario ↗</Text>
              </TouchableOpacity>
            </View>

            {/* Main Action: Escáner de Códigos UPC */}
            <Card
              style={{
                backgroundColor: colors.primary,
                borderColor: colors.primaryDark,
                padding: 18,
              }}
            >
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                <View style={{ flex: 1, paddingRight: 10 }}>
                  <Text style={{ color: '#FFFFFF', fontSize: 17, fontWeight: '800' }}>
                    📷 Escáner UPC & Reposición
                  </Text>
                  <Text style={{ color: '#E8F8F0', fontSize: 12, marginTop: 4 }}>
                    Escanear códigos de barra con la cámara, auditar lotes FEFO y reponer góndolas.
                  </Text>
                </View>
                <Button
                  title="Abrir"
                  variant="secondary"
                  size="md"
                  onPress={() => setScannerVisible(true)}
                />
              </View>
            </Card>

            {/* Tasks Checklist */}
            <View>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 }}>
                <Text style={Typography.h3(colors.text)}>
                  📋 Tareas Asignadas ({tasks.length})
                </Text>
                <Text style={{ fontSize: 12, color: colors.textMuted }}>
                  Semana 47 • Hoy
                </Text>
              </View>

              {tasks.map((task) => {
                const isDone = task.status === 'completed';
                const prioColor = task.priority === 'urgent' ? colors.danger : (task.priority === 'high' ? colors.warning : colors.info);
                return (
                  <Card
                    key={task.id}
                    style={{
                      marginBottom: 10,
                      borderLeftWidth: 4,
                      borderLeftColor: prioColor,
                      opacity: isDone ? 0.75 : 1,
                    }}
                  >
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                      <TouchableOpacity
                        onPress={() => handleToggleTask(task)}
                        style={{ flexDirection: 'row', alignItems: 'center', gap: 10, flex: 1 }}
                      >
                        <Text style={{ fontSize: 20 }}>{isDone ? '☑️' : '⬜'}</Text>
                        <View style={{ flex: 1 }}>
                          <Text style={[Typography.h3(colors.text), { fontSize: 14, textDecorationLine: isDone ? 'line-through' : 'none' }]}>
                            {task.title}
                          </Text>
                          <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
                            {task.description}
                          </Text>
                          {task.notes ? (
                            <Text style={{ fontSize: 11, color: colors.primary, marginTop: 4, fontWeight: '600' }}>
                              💡 {task.notes}
                            </Text>
                          ) : null}
                        </View>
                      </TouchableOpacity>

                      <Badge
                        label={task.priority}
                        variant={task.priority === 'urgent' ? 'danger' : (task.priority === 'high' ? 'warning' : 'neutral')}
                      />
                    </View>
                  </Card>
                );
              })}
            </View>
          </View>
        )}

        {/* =========================================================================
            ROLE VIEW 2: VENDEDOR COMERCIAL / SUPERVISOR / ADMIN DASHBOARD
           ========================================================================= */}
        {settings.role !== 'merchandiser' && (
          <>
            {/* Priority Action: Nuevo Pedido */}
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
                    Cotización rápida en 3 pasos en USD ($) con validación de crédito en Florida.
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
              📈 Resumen de Operaciones (USD)
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
                subtitle="Cartera Florida"
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

            {/* Alerts & Commercial Intelligence */}
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

            {alerts.slice(0, 2).map((alert) => (
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
            ))}

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
          </>
        )}
      </ScrollView>

      {/* =========================================================================
          MODAL: ESCÁNER DE CÓDIGOS UPC & REPOSICIÓN FEFO
         ========================================================================= */}
      <Modal
        visible={scannerVisible}
        animationType="slide"
        onRequestClose={() => setScannerVisible(false)}
      >
        <SafeAreaView style={{ flex: 1, backgroundColor: '#0B0F19' }}>
          {/* Scanner Header */}
          <View style={{ paddingHorizontal: 20, paddingVertical: 14, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: '#1F2937' }}>
            <View>
              <Text style={{ color: '#FFFFFF', fontSize: 16, fontWeight: '700' }}>
                📷 Escáner UPC & Auditoría FEFO
              </Text>
              <Text style={{ color: '#9CA3AF', fontSize: 11 }}>
                Prince Distributors — Hallandale Beach, FL
              </Text>
            </View>
            <TouchableOpacity
              onPress={() => {
                setScannerVisible(false);
                setScannedProduct(null);
                setScanMessage('');
              }}
              style={{ padding: 8, backgroundColor: '#374151', borderRadius: 20 }}
            >
              <Text style={{ color: '#FFFFFF', fontWeight: '700' }}>✕ Cerrar</Text>
            </TouchableOpacity>
          </View>

          {/* Mode Switcher: Cámara vs Ingreso Manual */}
          <View style={{ flexDirection: 'row', padding: 12, gap: 10, backgroundColor: '#111827' }}>
            <TouchableOpacity
              onPress={() => setScannerMode('camera')}
              style={{
                flex: 1,
                paddingVertical: 8,
                borderRadius: 8,
                backgroundColor: scannerMode === 'camera' ? colors.primary : '#1F2937',
                alignItems: 'center',
              }}
            >
              <Text style={{ color: '#FFFFFF', fontWeight: '700', fontSize: 12 }}>📹 Cámara en Vivo</Text>
            </TouchableOpacity>
            <TouchableOpacity
              onPress={() => setScannerMode('manual')}
              style={{
                flex: 1,
                paddingVertical: 8,
                borderRadius: 8,
                backgroundColor: scannerMode === 'manual' ? colors.primary : '#1F2937',
                alignItems: 'center',
              }}
            >
              <Text style={{ color: '#FFFFFF', fontWeight: '700', fontSize: 12 }}>⌨️ Ingreso Manual / UPC</Text>
            </TouchableOpacity>
          </View>

          {/* Scanner Body */}
          <ScrollView contentContainerStyle={{ padding: 16 }}>
            {scannerMode === 'camera' ? (
              <View style={{ borderRadius: 16, overflow: 'hidden', height: 260, backgroundColor: '#000000', justifyContent: 'center', alignItems: 'center' }}>
                {permission?.granted ? (
                  <CameraView
                    style={{ width: '100%', height: '100%' }}
                    barcodeScannerSettings={{
                      barcodeTypes: ['upc_a', 'upc_e', 'ean13', 'ean8', 'code128'],
                    }}
                    onBarcodeScanned={(result) => {
                      if (result.data) {
                        handleLookupUpc(result.data);
                      }
                    }}
                  />
                ) : (
                  <View style={{ padding: 20, alignItems: 'center' }}>
                    <Text style={{ color: '#FFFFFF', fontSize: 14, fontWeight: '700', textAlign: 'center', marginBottom: 10 }}>
                      Acceso a Cámara para Escaneo
                    </Text>
                    <Text style={{ color: '#9CA3AF', fontSize: 12, textAlign: 'center', marginBottom: 14 }}>
                      Permite escanear los códigos UPC en tiempo real o utiliza el modo manual alternativo.
                    </Text>
                    <Button
                      title="Solicitar Permiso de Cámara"
                      variant="primary"
                      size="sm"
                      onPress={requestPermission}
                    />
                  </View>
                )}
              </View>
            ) : null}

            {/* Manual Code Input & Quick Pick */}
            <View style={{ marginTop: 14, backgroundColor: '#1E293B', padding: 14, borderRadius: 12 }}>
              <Text style={{ color: '#94A3B8', fontSize: 12, fontWeight: '600', marginBottom: 6 }}>
                Ingresar Código UPC o SKU
              </Text>
              <View style={{ flexDirection: 'row', gap: 8 }}>
                <TextInput
                  value={manualUpcInput}
                  onChangeText={setManualUpcInput}
                  placeholder="Ej: 012000000133"
                  placeholderTextColor="#64748B"
                  style={{
                    flex: 1,
                    backgroundColor: '#0F172A',
                    color: '#FFFFFF',
                    borderRadius: 8,
                    paddingHorizontal: 12,
                    paddingVertical: 10,
                    fontSize: 14,
                    fontFamily: 'monospace',
                  }}
                />
                <Button
                  title="Buscar"
                  variant="primary"
                  size="sm"
                  onPress={() => handleLookupUpc(manualUpcInput)}
                />
              </View>

              {/* Quick Sample UPC Buttons */}
              <Text style={{ color: '#94A3B8', fontSize: 11, fontWeight: '600', marginTop: 12, marginBottom: 6 }}>
                Códigos de Prueba Auténticos:
              </Text>
              <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 6 }}>
                {quickUpcs.map((item) => (
                  <TouchableOpacity
                    key={item.code}
                    onPress={() => {
                      setManualUpcInput(item.code);
                      handleLookupUpc(item.code);
                    }}
                    style={{
                      backgroundColor: '#334155',
                      paddingHorizontal: 10,
                      paddingVertical: 6,
                      borderRadius: 6,
                    }}
                  >
                    <Text style={{ color: '#E2E8F0', fontSize: 11, fontWeight: '600' }}>
                      {item.name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>

            {scanMessage ? (
              <Text style={{ color: '#38BDF8', fontSize: 12, marginTop: 10, fontWeight: '600', textAlign: 'center' }}>
                {scanMessage}
              </Text>
            ) : null}

            {/* Scanned Product Card with FEFO and Replenishment Actions */}
            {scannedProduct ? (
              <View style={{ marginTop: 14, backgroundColor: '#0F172A', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: colors.primary }}>
                <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <View style={{ flex: 1 }}>
                    <Text style={{ color: colors.primary, fontSize: 11, fontWeight: '800', fontFamily: 'monospace' }}>
                      UPC: {scannedProduct.upc}
                    </Text>
                    <Text style={{ color: '#FFFFFF', fontSize: 16, fontWeight: '800', marginTop: 2 }}>
                      {scannedProduct.name}
                    </Text>
                    <Text style={{ color: '#94A3B8', fontSize: 12, marginTop: 1 }}>
                      {scannedProduct.category} • SKU: {scannedProduct.sku}
                    </Text>
                  </View>
                  <Text style={{ color: '#34D399', fontSize: 16, fontWeight: '800' }}>
                    ${scannedProduct.price.toFixed(2)}
                  </Text>
                </View>

                {/* FEFO Lot & Expiry Tag */}
                <View style={{ flexDirection: 'row', gap: 8, marginTop: 10, paddingVertical: 8, borderTopWidth: 1, borderTopColor: '#1E293B' }}>
                  <View style={{ flex: 1 }}>
                    <Text style={{ color: '#94A3B8', fontSize: 10, textTransform: 'uppercase' }}>Lote FEFO</Text>
                    <Text style={{ color: '#F1F5F9', fontSize: 13, fontWeight: '700', fontFamily: 'monospace' }}>
                      {scannedProduct.lot}
                    </Text>
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={{ color: '#94A3B8', fontSize: 10, textTransform: 'uppercase' }}>Vencimiento</Text>
                    <Text style={{ color: '#F87171', fontSize: 13, fontWeight: '700' }}>
                      {scannedProduct.expiration_date}
                    </Text>
                  </View>
                </View>

                {/* Stock Bodega vs Góndola */}
                <View style={{ flexDirection: 'row', gap: 10, marginTop: 6, backgroundColor: '#1E293B', padding: 12, borderRadius: 10 }}>
                  <View style={{ flex: 1, alignItems: 'center' }}>
                    <Text style={{ color: '#94A3B8', fontSize: 11 }}>En Bodega</Text>
                    <Text style={{ color: '#FFFFFF', fontSize: 18, fontWeight: '800' }}>
                      {scannedProduct.warehouse_stock}
                    </Text>
                  </View>
                  <View style={{ width: 1, backgroundColor: '#334155' }} />
                  <View style={{ flex: 1, alignItems: 'center' }}>
                    <Text style={{ color: '#94A3B8', fontSize: 11 }}>En Góndola</Text>
                    <Text style={{ color: '#34D399', fontSize: 18, fontWeight: '800' }}>
                      {scannedProduct.shelf_stock} / {scannedProduct.shelf_capacity}
                    </Text>
                  </View>
                </View>

                {/* Actions: Restock & Merma */}
                <View style={{ flexDirection: 'row', gap: 10, marginTop: 14 }}>
                  <Button
                    title="📦 +10 a Góndola"
                    variant="primary"
                    size="md"
                    style={{ flex: 1 }}
                    onPress={() => handleRestock(scannedProduct.id, 10)}
                  />
                  <Button
                    title="⚠️ Registrar Merma"
                    variant="outline"
                    size="md"
                    style={{ flex: 1 }}
                    onPress={() => handleShrinkage(scannedProduct.id)}
                  />
                </View>
              </View>
            ) : null}
          </ScrollView>
        </SafeAreaView>
      </Modal>

      {/* Weekly Schedule Modal */}
      <Modal visible={scheduleModalVisible} transparent animationType="slide" onRequestClose={() => setScheduleModalVisible(false)}>
        <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'flex-end' }}>
          <View style={{ backgroundColor: colors.card, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, maxHeight: '85%' }}>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 }}>
              <View>
                <Text style={{ fontSize: 11, fontWeight: '800', color: colors.primary, letterSpacing: 1 }}>
                  PROGRAMACIÓN SEMANAL WFM
                </Text>
                <Text style={Typography.h3(colors.text)}>
                  Horario — Rosa María Huamán
                </Text>
                <Text style={{ fontSize: 11, color: colors.textMuted }}>
                  Sede: Prince Distributors — Hallandale HQ (Semana 47)
                </Text>
              </View>
              <TouchableOpacity onPress={() => setScheduleModalVisible(false)} style={{ padding: 8 }}>
                <Text style={{ fontSize: 18, color: colors.textMuted }}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={{ marginBottom: 14 }}>
              {[
                { day: 'Lunes 16 Nov', hours: '09:00 AM – 05:30 PM', label: 'Intermedio', net: '8.0h', color: '#3B82F6' },
                { day: 'Martes 17 Nov (Hoy)', hours: '09:00 AM – 05:30 PM', label: 'Intermedio • Activo', net: '8.0h', color: '#00A859' },
                { day: 'Miércoles 18 Nov', hours: '09:00 AM – 05:30 PM', label: 'Intermedio', net: '8.0h', color: '#3B82F6' },
                { day: 'Jueves 19 Nov', hours: '09:00 AM – 05:30 PM', label: 'Intermedio', net: '8.0h', color: '#3B82F6' },
                { day: 'Viernes 20 Nov', hours: '09:00 AM – 05:30 PM', label: 'Intermedio', net: '8.0h', color: '#3B82F6' },
                { day: 'Sábado 21 Nov', hours: '09:00 AM – 05:30 PM', label: 'Intermedio', net: '8.0h', color: '#3B82F6' },
                { day: 'Domingo 22 Nov', hours: 'Descanso Semanal', label: 'Libre', net: '0.0h', color: '#94A3B8' },
              ].map((s, idx) => (
                <View
                  key={idx}
                  style={{
                    padding: 12,
                    borderRadius: 10,
                    backgroundColor: colors.background,
                    borderWidth: 1,
                    borderColor: s.day.includes('Hoy') ? '#00A859' : colors.border,
                    marginBottom: 8,
                    flexDirection: 'row',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                  }}
                >
                  <View>
                    <Text style={{ fontSize: 13, fontWeight: '700', color: s.day.includes('Hoy') ? '#00A859' : colors.text }}>
                      {s.day}
                    </Text>
                    <Text style={{ fontSize: 12, color: colors.textMuted, marginTop: 2 }}>
                      {s.hours}
                    </Text>
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text style={{ fontSize: 12, fontWeight: '800', color: colors.text }}>
                      {s.net}
                    </Text>
                    <Text style={{ fontSize: 10, color: s.color, fontWeight: '700' }}>
                      {s.label}
                    </Text>
                  </View>
                </View>
              ))}
            </ScrollView>

            <Button
              title="Cerrar Horario"
              variant="outline"
              size="md"
              onPress={() => setScheduleModalVisible(false)}
            />
          </View>
        </View>
      </Modal>

      {/* =========================================================================
          MODAL: CAMBIADOR RÁPIDO DE ROL
         ========================================================================= */}
      <Modal
        visible={roleModalVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setRoleModalVisible(false)}
      >
        <TouchableOpacity
          activeOpacity={1}
          onPress={() => setRoleModalVisible(false)}
          style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20 }}
        >
          <View style={{ backgroundColor: colors.card, borderRadius: 16, padding: 18, borderWidth: 1, borderColor: colors.border }}>
            <Text style={[Typography.h3(colors.text), { marginBottom: 4 }]}>
              Seleccionar Rol Activo
            </Text>
            <Text style={[Typography.caption(colors.textMuted), { marginBottom: 14 }]}>
              Cambia instantáneamente la vista para auditar permisos y flujos operacionales.
            </Text>

            {(['merchandiser', 'sales_rep', 'supervisor', 'admin'] as UserRole[]).map((r) => {
              const isSelected = settings.role === r;
              return (
                <TouchableOpacity
                  key={r}
                  onPress={() => {
                    updateSettings({ role: r });
                    setRoleModalVisible(false);
                  }}
                  style={{
                    padding: 12,
                    borderRadius: 10,
                    backgroundColor: isSelected ? colors.primaryLight : colors.background,
                    borderWidth: 1.5,
                    borderColor: isSelected ? colors.primary : colors.border,
                    marginBottom: 8,
                    flexDirection: 'row',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                  }}
                >
                  <View>
                    <Text style={{ fontSize: 14, fontWeight: '700', color: isSelected ? colors.primary : colors.text }}>
                      {roleLabels[r].icon} {roleLabels[r].label}
                    </Text>
                    <Text style={{ fontSize: 11, color: colors.textMuted, marginTop: 2 }}>
                      {r === 'merchandiser' ? 'Flujo MI JORNADA, escaneo UPC y reposición' :
                       r === 'sales_rep' ? 'Pedidos, cotizaciones y catálogo' :
                       r === 'supervisor' ? 'Supervisión de ruta y equipo comercial' :
                       'Acceso total corporativo WFM'}
                    </Text>
                  </View>
                  {isSelected ? <Text style={{ color: colors.primary, fontSize: 16, fontWeight: 'bold' }}>✔</Text> : null}
                </TouchableOpacity>
              );
            })}
          </View>
        </TouchableOpacity>
      </Modal>
    </SafeAreaView>
  );
}
