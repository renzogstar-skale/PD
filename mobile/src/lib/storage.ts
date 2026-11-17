/**
 * Prince Distributors B2B — Local Persistence & Offline Storage
 */

import AsyncStorage from '@react-native-async-storage/async-storage';
import { Customer, Product, Order, UserRole } from '../types';

const KEYS = {
  SETTINGS: 'pd_b2b_settings_usa_v1',
  OFFLINE_DRAFTS: 'pd_b2b_offline_drafts',
  CACHED_CUSTOMERS: 'pd_b2b_cached_customers',
  CACHED_PRODUCTS: 'pd_b2b_cached_products',
  API_KEY: 'pd_b2b_api_key',
  TASKS: 'pd_b2b_tasks_v1',
  INVENTORY: 'pd_b2b_inventory_v1',
  ACTIVE_SESSION: 'pd_b2b_active_session',
};

export interface AppSettings {
  theme: 'light' | 'dark' | 'system';
  language: 'es' | 'en';
  role: UserRole;
  apiBaseUrl: string;
  autoSync: boolean;
}

export const DEFAULT_SETTINGS: AppSettings = {
  theme: 'light',
  language: 'es',
  role: 'sales_rep',
  apiBaseUrl: 'https://pd-khaki-eight.vercel.app/api',
  autoSync: true,
};

export const Storage = {
  async getSettings(): Promise<AppSettings> {
    try {
      const data = await AsyncStorage.getItem(KEYS.SETTINGS);
      return data ? { ...DEFAULT_SETTINGS, ...JSON.parse(data) } : DEFAULT_SETTINGS;
    } catch {
      return DEFAULT_SETTINGS;
    }
  },

  async saveSettings(settings: Partial<AppSettings>): Promise<AppSettings> {
    const current = await this.getSettings();
    const updated = { ...current, ...settings };
    await AsyncStorage.setItem(KEYS.SETTINGS, JSON.stringify(updated));
    return updated;
  },

  async getOfflineDrafts(): Promise<Order[]> {
    try {
      const data = await AsyncStorage.getItem(KEYS.OFFLINE_DRAFTS);
      return data ? JSON.parse(data) : [];
    } catch {
      return [];
    }
  },

  async saveOfflineDraft(order: Order): Promise<void> {
    const drafts = await this.getOfflineDrafts();
    const existingIndex = drafts.findIndex(d => d.id === order.id || d.order_number === order.order_number);
    if (existingIndex >= 0) {
      drafts[existingIndex] = order;
    } else {
      drafts.unshift(order);
    }
    await AsyncStorage.setItem(KEYS.OFFLINE_DRAFTS, JSON.stringify(drafts));
  },

  async removeOfflineDraft(orderId: number | string): Promise<void> {
    const drafts = await this.getOfflineDrafts();
    const filtered = drafts.filter(d => d.id !== orderId && d.order_number !== orderId);
    await AsyncStorage.setItem(KEYS.OFFLINE_DRAFTS, JSON.stringify(filtered));
  },

  async getCachedCustomers(): Promise<Customer[]> {
    try {
      const data = await AsyncStorage.getItem(KEYS.CACHED_CUSTOMERS);
      return data ? JSON.parse(data) : [];
    } catch {
      return [];
    }
  },

  async setCachedCustomers(customers: Customer[]): Promise<void> {
    await AsyncStorage.setItem(KEYS.CACHED_CUSTOMERS, JSON.stringify(customers));
  },

  async getCachedProducts(): Promise<Product[]> {
    try {
      const data = await AsyncStorage.getItem(KEYS.CACHED_PRODUCTS);
      return data ? JSON.parse(data) : [];
    } catch {
      return [];
    }
  },

  async setCachedProducts(products: Product[]): Promise<void> {
    await AsyncStorage.setItem(KEYS.CACHED_PRODUCTS, JSON.stringify(products));
  },

  async clearCache(): Promise<void> {
    await AsyncStorage.multiRemove([KEYS.CACHED_CUSTOMERS, KEYS.CACHED_PRODUCTS]);
  }
};
