/**
 * Prince Distributors B2B — Theme, i18n & Session Context
 */

import React, { createContext, useContext, useState, useEffect } from 'react';
import { useColorScheme } from 'react-native';
import { Storage, AppSettings, DEFAULT_SETTINGS } from './storage';
import { UserRole } from '../types';

export const Colors = {
  light: {
    primary: '#00A859', // Prince Corporate Emerald Green
    primaryDark: '#008746',
    primaryLight: '#E8F8F0',
    background: '#F8FAFC',
    card: '#FFFFFF',
    text: '#0F172A',
    textMuted: '#64748B',
    border: '#E2E8F0',
    success: '#10B981',
    warning: '#F59E0B',
    danger: '#EF4444',
    info: '#3B82F6',
    tabBar: '#FFFFFF',
    tabBarActive: '#00A859',
    tabBarInactive: '#94A3B8',
  },
  dark: {
    primary: '#00C86B',
    primaryDark: '#00A859',
    primaryLight: '#0D2619',
    background: '#0B130E',
    card: '#131E17',
    text: '#F8FAFC',
    textMuted: '#94A3B8',
    border: '#1F2E25',
    success: '#10B981',
    warning: '#FBBF24',
    danger: '#F87171',
    info: '#60A5FA',
    tabBar: '#101B14',
    tabBarActive: '#00C86B',
    tabBarInactive: '#64748B',
  }
};

const translations = {
  es: {
    // Tabs
    tab_home: 'Inicio',
    tab_customers: 'Clientes',
    tab_orders: 'Pedidos',
    tab_products: 'Productos',
    tab_more: 'Más',
    // Home
    home_title: 'Prince Distributors',
    home_welcome: 'Panel Comercial',
    kpi_today_sales: 'Ventas netas de hoy',
    kpi_orders: 'Pedidos de hoy',
    kpi_active_customers: 'Clientes activos',
    kpi_cxc: 'Cuentas por cobrar',
    btn_new_order: 'Nuevo Pedido',
    alerts_title: 'Alertas Comerciales',
    no_alerts: 'No hay alertas urgentes pendientes.',
    // Customers
    customers_title: 'Directorio de Clientes',
    search_customers: 'Buscar cliente, RUC o territorio...',
    credit_limit: 'Límite:',
    outstanding: 'Saldo:',
    btn_call: 'Llamar',
    btn_order_now: 'Pedir',
    // Products
    products_title: 'Catálogo de Productos',
    search_products: 'Buscar por SKU, producto o marca...',
    unit: 'Unidad',
    box: 'Caja',
    stock: 'Stock:',
    // Orders
    orders_title: 'Pedidos y Cotizaciones',
    order_step_1: '1. Seleccionar Cliente',
    order_step_2: '2. Agregar Productos',
    order_step_3: '3. Confirmar Pedido',
    confirm_order: 'Confirmar y Enviar',
    empty_cart: 'El carrito está vacío',
    subtotal: 'Subtotal',
    tax: 'IGV (18%)',
    total: 'Total',
    // More
    more_title: 'Módulos y Gestión',
    mod_invoices: 'Facturación y Cobranzas',
    mod_sales_reps: 'Fuerza de Ventas y Metas',
    mod_profitability: 'Rentabilidad y Márgenes',
    mod_intelligence: 'Inteligencia Comercial',
    mod_settings: 'Configuración y Sesión',
    // Settings
    settings_title: 'Configuración',
    setting_role: 'Rol Activo',
    setting_theme: 'Tema Visual',
    setting_lang: 'Idioma',
    setting_server: 'Servidor API',
    clear_cache: 'Limpiar Caché Local',
  },
  en: {
    tab_home: 'Home',
    tab_customers: 'Customers',
    tab_orders: 'Orders',
    tab_products: 'Products',
    tab_more: 'More',
    home_title: 'Prince Distributors',
    home_welcome: 'Commercial Dashboard',
    kpi_today_sales: "Today's net sales",
    kpi_orders: "Today's orders",
    kpi_active_customers: 'Active customers',
    kpi_cxc: 'Accounts receivable',
    btn_new_order: 'New Order',
    alerts_title: 'Commercial Alerts',
    no_alerts: 'No urgent alerts pending.',
    customers_title: 'Customer Directory',
    search_customers: 'Search client, tax ID, territory...',
    credit_limit: 'Credit Limit:',
    outstanding: 'Balance:',
    btn_call: 'Call',
    btn_order_now: 'Order',
    products_title: 'Product Catalog',
    search_products: 'Search SKU, product or brand...',
    unit: 'Unit',
    box: 'Box',
    stock: 'Stock:',
    orders_title: 'Orders & Quotes',
    order_step_1: '1. Select Customer',
    order_step_2: '2. Add Products',
    order_step_3: '3. Confirm Order',
    confirm_order: 'Confirm & Send',
    empty_cart: 'Cart is empty',
    subtotal: 'Subtotal',
    tax: 'Tax (18%)',
    total: 'Total',
    more_title: 'Modules & Tools',
    mod_invoices: 'Invoices & A/R',
    mod_sales_reps: 'Sales Team & Quotas',
    mod_profitability: 'Profitability & Margins',
    mod_intelligence: 'Commercial Intelligence',
    mod_settings: 'Settings & Session',
    settings_title: 'Settings',
    setting_role: 'Active Role',
    setting_theme: 'Visual Theme',
    setting_lang: 'Language',
    setting_server: 'API Server',
    clear_cache: 'Clear Local Cache',
  }
};

interface ThemeContextType {
  settings: AppSettings;
  colors: typeof Colors.light;
  isDark: boolean;
  t: (key: keyof typeof translations.es) => string;
  updateSettings: (newSettings: Partial<AppSettings>) => Promise<void>;
}

const ThemeContext = createContext<ThemeContextType>({
  settings: DEFAULT_SETTINGS,
  colors: Colors.light,
  isDark: false,
  t: (k) => translations.es[k] || k,
  updateSettings: async () => {},
});

export const ThemeProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const systemScheme = useColorScheme();
  const [settings, setSettings] = useState<AppSettings>(DEFAULT_SETTINGS);

  useEffect(() => {
    Storage.getSettings().then(setSettings);
  }, []);

  const isDark = settings.theme === 'dark' || (settings.theme === 'system' && systemScheme === 'dark');
  const colors = isDark ? Colors.dark : Colors.light;

  const t = (key: keyof typeof translations.es): string => {
    const lang = settings.language || 'es';
    return translations[lang]?.[key] || translations.es[key] || key;
  };

  const updateSettings = async (newSettings: Partial<AppSettings>) => {
    const updated = await Storage.saveSettings(newSettings);
    setSettings(updated);
  };

  return (
    <ThemeContext.Provider value={{ settings, colors, isDark, t, updateSettings }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => useContext(ThemeContext);
