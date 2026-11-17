/**
 * Prince Distributors B2B — Mobile Types & Domain Entities
 */

export type UserRole = 'sales_rep' | 'supervisor' | 'admin';

export interface CustomerEstablishment {
  id: number;
  customer_id: number;
  name: string;
  address: string;
  city?: string;
  phone?: string;
  is_main: boolean | number;
}

export interface Customer {
  id: number;
  code: string;
  business_name: string;
  commercial_name?: string;
  tax_id: string; // RUC / Tax ID
  email?: string;
  phone?: string;
  address?: string;
  territory?: string;
  sales_rep_name?: string;
  credit_limit: number;
  outstanding_balance: number;
  payment_terms_days: number;
  price_list_id?: number;
  is_active: boolean | number;
  establishments?: CustomerEstablishment[];
  last_purchase_date?: string;
}

export interface Product {
  id: number;
  sku: string;
  name: string;
  description?: string;
  category: string;
  brand: string;
  unit_price: number;
  box_price: number;
  units_per_box: number;
  current_stock: number;
  min_order_qty: number;
  historical_cost?: number;
  image_url?: string;
  is_active: boolean | number;
}

export interface OrderItem {
  product_id: number;
  product_sku: string;
  product_name: string;
  package_type: 'unit' | 'box';
  quantity: number;
  unit_price: number;
  discount_percent: number;
  tax_percent: number;
  subtotal: number;
  total: number;
}

export interface Order {
  id: number;
  order_number: string;
  customer_id: number;
  customer_name: string;
  establishment_id?: number;
  establishment_name?: string;
  status: 'draft' | 'confirmed' | 'processing' | 'dispatched' | 'invoiced' | 'cancelled';
  items_count: number;
  subtotal: number;
  tax_amount: number;
  total_amount: number;
  created_at: string;
  notes?: string;
  is_offline_draft?: boolean;
}

export interface Invoice {
  id: number;
  invoice_number: string;
  order_id?: number;
  customer_id: number;
  customer_name: string;
  issue_date: string;
  due_date: string;
  total_amount: number;
  paid_amount: number;
  balance: number;
  status: 'paid' | 'partial' | 'pending' | 'overdue';
}

export interface SalesRep {
  id: number;
  name: string;
  code: string;
  email: string;
  phone?: string;
  territory: string;
  monthly_target: number;
  current_sales: number;
  target_achievement_pct: number;
  commission_earned: number;
  active_customers_count: number;
}

export interface CommercialAlert {
  id: number;
  type: 'inactive_customer' | 'sales_drop' | 'pending_followup' | 'overdue_invoice' | 'negative_margin';
  title: string;
  description: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  customer_id?: number;
  customer_name?: string;
  action_suggested: string;
  created_at: string;
  is_reviewed: boolean;
}

export interface ProfitabilityMetrics {
  total_sales: number;
  total_cost: number;
  gross_profit: number;
  gross_margin_pct: number;
  top_profitable_category: string;
  top_profitable_product: string;
}

export interface SalesOverviewKPIs {
  today_sales: number;
  today_orders_count: number;
  month_sales: number;
  active_customers_count: number;
  total_cxc: number; // Accounts receivable
  critical_alerts_count: number;
}
