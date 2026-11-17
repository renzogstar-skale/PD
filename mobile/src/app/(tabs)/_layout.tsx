/**
 * Prince Distributors B2B — Bottom Tabs Layout (5 Approved Tabs)
 */

import React from 'react';
import { Tabs } from 'expo-router';
import { Text, Platform, View } from 'react-native';
import { useTheme } from '../../lib/theme';

interface TabIconProps {
  icon: string;
  focused: boolean;
  color?: any;
}

const TabIcon: React.FC<TabIconProps> = ({ icon, focused }) => {
  return (
    <View
      style={{
        alignItems: 'center',
        justifyContent: 'center',
        paddingTop: Platform.OS === 'ios' ? 4 : 0,
      }}
    >
      <Text style={{ fontSize: 20, opacity: focused ? 1 : 0.7 }}>{icon}</Text>
    </View>
  );
};

export default function TabsLayout() {
  const { colors, t } = useTheme();

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.tabBarActive,
        tabBarInactiveTintColor: colors.tabBarInactive,
        tabBarStyle: {
          backgroundColor: colors.tabBar,
          borderTopColor: colors.border,
          borderTopWidth: 1,
          height: Platform.OS === 'ios' ? 88 : 64,
          paddingBottom: Platform.OS === 'ios' ? 28 : 10,
          paddingTop: 8,
          elevation: 8,
          shadowColor: '#000',
          shadowOffset: { width: 0, height: -2 },
          shadowOpacity: 0.05,
          shadowRadius: 4,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '600',
          marginTop: 2,
        },
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: t('tab_home'),
          tabBarIcon: (props) => <TabIcon icon="📊" {...props} />,
        }}
      />
      <Tabs.Screen
        name="customers"
        options={{
          title: t('tab_customers'),
          tabBarIcon: (props) => <TabIcon icon="👥" {...props} />,
        }}
      />
      <Tabs.Screen
        name="orders"
        options={{
          title: t('tab_orders'),
          tabBarIcon: (props) => <TabIcon icon="📦" {...props} />,
        }}
      />
      <Tabs.Screen
        name="products"
        options={{
          title: t('tab_products'),
          tabBarIcon: (props) => <TabIcon icon="🏷️" {...props} />,
        }}
      />
      <Tabs.Screen
        name="more"
        options={{
          title: t('tab_more'),
          tabBarIcon: (props) => <TabIcon icon="☰" {...props} />,
        }}
      />
    </Tabs>
  );
}
