/**
 * Prince Distributors B2B — Mobile-First Reusable UI Components
 */

import React from 'react';
import {
  View,
  Text as RNText,
  TouchableOpacity,
  TextInput,
  StyleSheet,
  ActivityIndicator,
  ViewStyle,
  TextStyle,
} from 'react-native';
import { useTheme } from '../lib/theme';

// --- TYPOGRAPHY ---
export const Typography = {
  h1: (color: string): TextStyle => ({
    fontSize: 24,
    fontWeight: '700',
    color,
    letterSpacing: -0.5,
  }),
  h2: (color: string): TextStyle => ({
    fontSize: 20,
    fontWeight: '700',
    color,
    letterSpacing: -0.3,
  }),
  h3: (color: string): TextStyle => ({
    fontSize: 16,
    fontWeight: '600',
    color,
  }),
  body: (color: string): TextStyle => ({
    fontSize: 14,
    color,
    lineHeight: 20,
  }),
  caption: (color: string): TextStyle => ({
    fontSize: 12,
    color,
  }),
  bold: {
    fontWeight: '700' as const,
  },
  semiBold: {
    fontWeight: '600' as const,
  },
};

// --- CARD ---
export interface CardProps {
  children: React.ReactNode;
  style?: ViewStyle;
  onPress?: () => void;
  variant?: 'elevated' | 'outlined' | 'flat';
}

export const Card: React.FC<CardProps> = ({ children, style, onPress, variant = 'elevated' }) => {
  const { colors, isDark } = useTheme();

  const cardStyle: ViewStyle = {
    backgroundColor: colors.card,
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: isDark ? colors.border : '#EDF2F7',
    ...(variant === 'elevated' && !isDark
      ? {
          shadowColor: '#000',
          shadowOffset: { width: 0, height: 2 },
          shadowOpacity: 0.05,
          shadowRadius: 6,
          elevation: 2,
        }
      : {}),
    ...style,
  };

  if (onPress) {
    return (
      <TouchableOpacity activeOpacity={0.75} onPress={onPress} style={cardStyle}>
        {children}
      </TouchableOpacity>
    );
  }

  return <View style={cardStyle}>{children}</View>;
};

// --- BUTTON ---
export interface ButtonProps {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'secondary' | 'outline' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  disabled?: boolean;
  icon?: string;
  style?: ViewStyle;
}

export const Button: React.FC<ButtonProps> = ({
  title,
  onPress,
  variant = 'primary',
  size = 'md',
  loading = false,
  disabled = false,
  icon,
  style,
}) => {
  const { colors } = useTheme();

  let bg = colors.primary;
  let textCol = '#FFFFFF';
  let borderCol = 'transparent';

  if (variant === 'secondary') {
    bg = colors.primaryLight;
    textCol = colors.primary;
  } else if (variant === 'outline') {
    bg = 'transparent';
    textCol = colors.primary;
    borderCol = colors.primary;
  } else if (variant === 'danger') {
    bg = colors.danger;
    textCol = '#FFFFFF';
  } else if (variant === 'ghost') {
    bg = 'transparent';
    textCol = colors.textMuted;
  }

  const height = size === 'sm' ? 38 : size === 'lg' ? 54 : 46;
  const paddingH = size === 'sm' ? 12 : size === 'lg' ? 24 : 18;

  return (
    <TouchableOpacity
      activeOpacity={0.8}
      onPress={onPress}
      disabled={disabled || loading}
      style={[
        {
          backgroundColor: disabled ? '#CBD5E1' : bg,
          height,
          paddingHorizontal: paddingH,
          borderRadius: 12,
          borderWidth: variant === 'outline' ? 1.5 : 0,
          borderColor: borderCol,
          flexDirection: 'row',
          alignItems: 'center',
          justifyContent: 'center',
          opacity: disabled ? 0.6 : 1,
        },
        style,
      ]}
    >
      {loading ? (
        <ActivityIndicator color={textCol} size="small" />
      ) : (
        <View style={{ flexDirection: 'row', alignItems: 'center' }}>
          {icon ? <RNText style={{ marginRight: 8, fontSize: 16 }}>{icon}</RNText> : null}
          <RNText
            style={{
              color: textCol,
              fontSize: size === 'sm' ? 13 : size === 'lg' ? 16 : 14,
              fontWeight: '600',
            }}
          >
            {title}
          </RNText>
        </View>
      )}
    </TouchableOpacity>
  );
};

// --- BADGE ---
export interface BadgeProps {
  label: string;
  variant?: 'success' | 'warning' | 'danger' | 'info' | 'neutral';
  size?: 'sm' | 'md';
}

export const Badge: React.FC<BadgeProps> = ({ label, variant = 'neutral', size = 'sm' }) => {
  const { colors, isDark } = useTheme();

  let bg = '#F1F5F9';
  let text = '#475569';

  if (variant === 'success') {
    bg = isDark ? '#064E3B' : '#DEF7EC';
    text = isDark ? '#34D399' : '#03543F';
  } else if (variant === 'warning') {
    bg = isDark ? '#78350F' : '#FEF3C7';
    text = isDark ? '#FBBF24' : '#92400E';
  } else if (variant === 'danger') {
    bg = isDark ? '#7F1D1D' : '#FDE8E8';
    text = isDark ? '#F87171' : '#9B1C1C';
  } else if (variant === 'info') {
    bg = isDark ? '#1E3A8A' : '#E1EFFE';
    text = isDark ? '#60A5FA' : '#1E429F';
  }

  return (
    <View
      style={{
        backgroundColor: bg,
        paddingHorizontal: size === 'sm' ? 8 : 12,
        paddingVertical: size === 'sm' ? 4 : 6,
        borderRadius: 20,
        alignSelf: 'flex-start',
      }}
    >
      <RNText
        style={{
          color: text,
          fontSize: size === 'sm' ? 11 : 13,
          fontWeight: '600',
          textTransform: 'uppercase',
          letterSpacing: 0.5,
        }}
      >
        {label}
      </RNText>
    </View>
  );
};

// --- SEARCH BAR ---
export interface SearchBarProps {
  value: string;
  onChangeText: (text: string) => void;
  placeholder?: string;
  style?: ViewStyle;
}

export const SearchBar: React.FC<SearchBarProps> = ({
  value,
  onChangeText,
  placeholder = 'Buscar...',
  style,
}) => {
  const { colors, isDark } = useTheme();

  return (
    <View
      style={[
        {
          flexDirection: 'row',
          alignItems: 'center',
          backgroundColor: isDark ? colors.card : '#F1F5F9',
          borderRadius: 14,
          paddingHorizontal: 14,
          height: 46,
          borderWidth: 1,
          borderColor: isDark ? colors.border : '#E2E8F0',
        },
        style,
      ]}
    >
      <RNText style={{ fontSize: 16, marginRight: 8, color: colors.textMuted }}>🔍</RNText>
      <TextInput
        value={value}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={colors.textMuted}
        style={{
          flex: 1,
          color: colors.text,
          fontSize: 14,
          paddingVertical: 0,
        }}
      />
      {value ? (
        <TouchableOpacity onPress={() => onChangeText('')} style={{ padding: 4 }}>
          <RNText style={{ color: colors.textMuted, fontSize: 14 }}>✕</RNText>
        </TouchableOpacity>
      ) : null}
    </View>
  );
};

// --- STAT KPI CARD ---
export interface StatCardProps {
  title: string;
  value: string;
  icon: string;
  subtitle?: string;
  color?: string;
  style?: ViewStyle;
}

export const StatCard: React.FC<StatCardProps> = ({
  title,
  value,
  icon,
  subtitle,
  color,
  style,
}) => {
  const { colors } = useTheme();
  const accent = color || colors.primary;

  return (
    <Card style={{ flex: 1, minWidth: 140, ...(style || {}) }}>
      <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
        <RNText style={Typography.caption(colors.textMuted)}>{title}</RNText>
        <RNText style={{ fontSize: 18 }}>{icon}</RNText>
      </View>
      <RNText style={[Typography.h2(accent), { marginVertical: 4 }]}>{value}</RNText>
      {subtitle ? <RNText style={Typography.caption(colors.textMuted)}>{subtitle}</RNText> : null}
    </Card>
  );
};

// --- SKELETON ---
export const Skeleton: React.FC<{ width?: number | string; height?: number; borderRadius?: number; style?: ViewStyle }> = ({
  width = '100%',
  height = 20,
  borderRadius = 8,
  style,
}) => {
  const { isDark } = useTheme();
  return (
    <View
      style={[
        {
          width: width as any,
          height,
          borderRadius,
          backgroundColor: isDark ? '#1F2E25' : '#E2E8F0',
        },
        style,
      ]}
    />
  );
};

// --- HEADER ---
export interface HeaderProps {
  title: string;
  subtitle?: string;
  onBack?: () => void;
  rightAction?: React.ReactNode;
}

export const Header: React.FC<HeaderProps> = ({ title, subtitle, onBack, rightAction }) => {
  const { colors } = useTheme();

  return (
    <View
      style={{
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 20,
        paddingTop: 16,
        paddingBottom: 12,
        backgroundColor: colors.card,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
      }}
    >
      <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
        {onBack ? (
          <TouchableOpacity onPress={onBack} style={{ paddingRight: 14 }}>
            <RNText style={{ fontSize: 22, color: colors.primary }}>‹</RNText>
          </TouchableOpacity>
        ) : null}
        <View style={{ flex: 1 }}>
          <RNText style={Typography.h2(colors.text)} numberOfLines={1}>
            {title}
          </RNText>
          {subtitle ? (
            <RNText style={Typography.caption(colors.textMuted)} numberOfLines={1}>
              {subtitle}
            </RNText>
          ) : null}
        </View>
      </View>
      {rightAction ? <View>{rightAction}</View> : null}
    </View>
  );
};
