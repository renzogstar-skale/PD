/**
 * Prince Distributors B2B — Supabase Client para React Native & Expo Go
 * Seguridad:
 * - Utiliza únicamente EXPO_PUBLIC_SUPABASE_URL y EXPO_PUBLIC_SUPABASE_ANON_KEY.
 * - NUNCA expone la clave service_role ni credenciales directas de PostgreSQL.
 * - Almacenamiento seguro de sesiones con Expo SecureStore en iOS/Android y AsyncStorage en Web.
 * - Soporte completo para Supabase Auth, Row Level Security (RLS) y Supabase Realtime.
 */

import AsyncStorage from '@react-native-async-storage/async-storage';
import * as SecureStore from 'expo-secure-store';
import { createClient, SupabaseClient } from '@supabase/supabase-js';
import { Platform } from 'react-native';

const ExpoSecureStoreAdapter = {
  getItem: async (key: string): Promise<string | null> => {
    try {
      if (Platform.OS === 'web') {
        return await AsyncStorage.getItem(key);
      }
      return await SecureStore.getItemAsync(key);
    } catch {
      return null;
    }
  },
  setItem: async (key: string, value: string): Promise<void> => {
    try {
      if (Platform.OS === 'web') {
        await AsyncStorage.setItem(key, value);
      } else {
        await SecureStore.setItemAsync(key, value);
      }
    } catch {
      // Ignore write errors
    }
  },
  removeItem: async (key: string): Promise<void> => {
    try {
      if (Platform.OS === 'web') {
        await AsyncStorage.removeItem(key);
      } else {
        await SecureStore.deleteItemAsync(key);
      }
    } catch {
      // Ignore removal errors
    }
  },
};

// Variables de entorno públicas para Expo
const DEFAULT_SUPABASE_URL = process.env.EXPO_PUBLIC_SUPABASE_URL || 'https://ulyeueevtbmogfdixybs.supabase.co';
const DEFAULT_SUPABASE_ANON_KEY = process.env.EXPO_PUBLIC_SUPABASE_ANON_KEY || 'sb_publishable_iHGvb3797aoQQDQmpkU1cA_mneRcvEZ';

let supabaseInstance: SupabaseClient | null = null;

export function getSupabaseClient(customUrl?: string, customAnonKey?: string): SupabaseClient {
  const url = customUrl || DEFAULT_SUPABASE_URL;
  const anonKey = customAnonKey || DEFAULT_SUPABASE_ANON_KEY;

  if (!supabaseInstance || customUrl || customAnonKey) {
    supabaseInstance = createClient(url, anonKey, {
      auth: {
        storage: ExpoSecureStoreAdapter,
        autoRefreshToken: true,
        persistSession: true,
        detectSessionInUrl: false,
      },
      realtime: {
        params: {
          eventsPerSecond: 10,
        },
      },
    });
  }

  return supabaseInstance;
}

export const supabase = getSupabaseClient();
