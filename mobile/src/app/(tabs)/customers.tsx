/**
 * Prince Distributors B2B — Tab 2: Clientes (Directory, Credit Status & Quick Actions)
 */

import React, { useState, useEffect, useMemo } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  Linking,
  SafeAreaView,
  ScrollView,
  RefreshControl,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTheme } from '../../lib/theme';
import { ApiClient } from '../../lib/api';
import { Card, Button, Badge, SearchBar, Skeleton, Typography } from '../../components/ui';
import { Customer } from '../../types';

export default function CustomersScreen() {
  const { colors, t } = useTheme();
  const router = useRouter();

  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [selectedTerritory, setSelectedTerritory] = useState('Todos');
  const [expandedId, setExpandedId] = useState<number | null>(null);

  const loadCustomers = async () => {
    try {
      const data = await ApiClient.getCustomers();
      setCustomers(data);
    } catch {
      // ApiClient handles fallback
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadCustomers();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadCustomers();
  };

  const territories = ['Todos', 'Lima Norte', 'Lima Centro', 'Lima Este', 'Lima Sur'];

  const filtered = useMemo(() => {
    return customers.filter((c) => {
      const q = search.toLowerCase();
      const matchSearch =
        !search ||
        c.business_name.toLowerCase().includes(q) ||
        (c.commercial_name && c.commercial_name.toLowerCase().includes(q)) ||
        c.tax_id.includes(q) ||
        (c.territory && c.territory.toLowerCase().includes(q));

      const matchTerritory =
        selectedTerritory === 'Todos' || c.territory === selectedTerritory;

      return matchSearch && matchTerritory;
    });
  }, [customers, search, selectedTerritory]);

  const handleCall = (phone?: string) => {
    if (phone) {
      Linking.openURL(`tel:${phone.replace(/\s+/g, '')}`);
    }
  };

  const handleStartOrder = (customer: Customer) => {
    router.push({
      pathname: '/new-order',
      params: { preselected_customer_id: customer.id, preselected_customer_name: customer.business_name },
    } as any);
  };

  const renderItem = ({ item }: { item: Customer }) => {
    const isExpanded = expandedId === item.id;
    const availableCredit = item.credit_limit - item.outstanding_balance;
    const creditPercent = Math.min(100, Math.round((item.outstanding_balance / (item.credit_limit || 1)) * 100));
    const isCreditRisk = creditPercent >= 90;

    return (
      <Card
        style={{
          marginBottom: 12,
          borderLeftWidth: 4,
          borderLeftColor: isCreditRisk ? colors.danger : colors.primary,
        }}
      >
        <TouchableOpacity
          activeOpacity={0.7}
          onPress={() => setExpandedId(isExpanded ? null : item.id)}
        >
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
            <View style={{ flex: 1, paddingRight: 8 }}>
              <Text style={Typography.h3(colors.text)} numberOfLines={1}>
                {item.business_name}
              </Text>
              {item.commercial_name ? (
                <Text style={[Typography.caption(colors.textMuted), { marginTop: 2 }]}>
                  {item.commercial_name} • RUC {item.tax_id}
                </Text>
              ) : null}
            </View>

            <Badge
              label={item.territory || 'Comercial'}
              variant={isCreditRisk ? 'warning' : 'neutral'}
            />
          </View>

          {/* Credit Health Bar */}
          <View style={{ marginTop: 12 }}>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 }}>
              <Text style={Typography.caption(colors.textMuted)}>
                Saldo: <Text style={{ fontWeight: '600', color: colors.text }}>${item.outstanding_balance.toFixed(2)}</Text>
              </Text>
              <Text style={Typography.caption(colors.textMuted)}>
                Límite: <Text style={{ fontWeight: '600', color: colors.text }}>${item.credit_limit.toFixed(2)}</Text>
              </Text>
            </View>

            <View
              style={{
                height: 6,
                backgroundColor: colors.border,
                borderRadius: 3,
                overflow: 'hidden',
              }}
            >
              <View
                style={{
                  height: '100%',
                  width: `${creditPercent}%`,
                  backgroundColor: isCreditRisk ? colors.danger : colors.primary,
                }}
              />
            </View>
          </View>
        </TouchableOpacity>

        {/* Expandable Section */}
        {isExpanded && (
          <View
            style={{
              marginTop: 12,
              paddingTop: 12,
              borderTopWidth: 1,
              borderTopColor: colors.border,
            }}
          >
            <Text style={[Typography.caption(colors.textMuted), { marginBottom: 4 }]}>
              📍 Dirección: {item.address}
            </Text>
            <Text style={[Typography.caption(colors.textMuted), { marginBottom: 4 }]}>
              📞 Teléfono: {item.phone || 'No registrado'}
            </Text>
            <Text style={[Typography.caption(colors.textMuted), { marginBottom: 4 }]}>
              💳 Crédito disponible: ${availableCredit.toFixed(2)} ({item.payment_terms_days} días de plazo)
            </Text>
            {item.last_purchase_date ? (
              <Text style={[Typography.caption(colors.textMuted), { marginBottom: 8 }]}>
                🛍️ Última compra: {item.last_purchase_date}
              </Text>
            ) : null}
          </View>
        )}

        {/* Action Row */}
        <View
          style={{
            flexDirection: 'row',
            gap: 10,
            marginTop: 12,
            paddingTop: 8,
            borderTopWidth: 1,
            borderTopColor: colors.border,
          }}
        >
          {item.phone ? (
            <Button
              title={t('btn_call')}
              variant="outline"
              size="sm"
              icon="📞"
              onPress={() => handleCall(item.phone)}
              style={{ flex: 1 }}
            />
          ) : null}

          <Button
            title="Crear Pedido"
            variant="primary"
            size="sm"
            icon="🛍️"
            onPress={() => handleStartOrder(item)}
            style={{ flex: 1.5 }}
          />
        </View>
      </Card>
    );
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }}>
      {/* Search Header */}
      <View
        style={{
          paddingHorizontal: 18,
          paddingTop: 14,
          paddingBottom: 10,
          backgroundColor: colors.card,
          borderBottomWidth: 1,
          borderBottomColor: colors.border,
        }}
      >
        <Text style={[Typography.h2(colors.text), { marginBottom: 10 }]}>
          👥 {t('customers_title')}
        </Text>
        <SearchBar
          value={search}
          onChangeText={setSearch}
          placeholder={t('search_customers')}
        />

        {/* Territory Filter Chips */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={{ marginTop: 10 }}
          contentContainerStyle={{ gap: 8 }}
        >
          {territories.map((tName) => {
            const isSelected = selectedTerritory === tName;
            return (
              <TouchableOpacity
                key={tName}
                onPress={() => setSelectedTerritory(tName)}
                style={{
                  paddingHorizontal: 12,
                  paddingVertical: 6,
                  borderRadius: 18,
                  backgroundColor: isSelected ? colors.primary : colors.background,
                  borderWidth: 1,
                  borderColor: isSelected ? colors.primary : colors.border,
                }}
              >
                <Text
                  style={{
                    fontSize: 12,
                    fontWeight: isSelected ? '700' : '500',
                    color: isSelected ? '#FFFFFF' : colors.textMuted,
                  }}
                >
                  {tName}
                </Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>

      {/* List */}
      {loading ? (
        <View style={{ padding: 18, gap: 12 }}>
          <Skeleton height={110} />
          <Skeleton height={110} />
          <Skeleton height={110} />
        </View>
      ) : (
        <FlatList
          data={filtered}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderItem}
          contentContainerStyle={{ padding: 18, paddingBottom: 30 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}
          ListEmptyComponent={
            <View style={{ alignItems: 'center', marginTop: 40, padding: 20 }}>
              <Text style={{ fontSize: 40, marginBottom: 12 }}>🔍</Text>
              <Text style={Typography.h3(colors.text)}>No se encontraron clientes</Text>
              <Text style={[Typography.caption(colors.textMuted), { textAlign: 'center', marginTop: 4 }]}>
                Intente con otro nombre, número de RUC o cambie el filtro de territorio.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}
