/**
 * Prince Distributors B2B — Root Stack Layout
 */

import React from 'react';
import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { ThemeProvider, useTheme } from '../lib/theme';

function NavigationStack() {
  const { isDark, colors } = useTheme();

  return (
    <>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <Stack
        screenOptions={{
          headerShown: false,
          contentStyle: { backgroundColor: colors.background },
          animation: 'slide_from_right',
        }}
      >
        <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
        <Stack.Screen
          name="new-order"
          options={{
            presentation: 'modal',
            animation: 'slide_from_bottom',
            headerShown: false,
          }}
        />
        <Stack.Screen name="invoices" options={{ headerShown: false }} />
        <Stack.Screen name="sales-reps" options={{ headerShown: false }} />
        <Stack.Screen name="profitability" options={{ headerShown: false }} />
        <Stack.Screen name="intelligence" options={{ headerShown: false }} />
        <Stack.Screen name="settings" options={{ headerShown: false }} />
      </Stack>
    </>
  );
}

export default function RootLayout() {
  return (
    <SafeAreaProvider>
      <ThemeProvider>
        <NavigationStack />
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
