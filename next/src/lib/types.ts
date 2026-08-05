export type InvoiceStatus = "pending" | "paid" | "cancelled";

export type ClientStatus = "active" | "inactive";

export type BillingPeriod = "m1" | "m3" | "m12";

export interface Client {
  id: string;
  name: string;
  company: string;
  email: string;
  site?: string;
  language: "en" | "uk" | "ru" | "no";
  status: ClientStatus;
  notes?: string;
  createdAt: string;
}

export interface Service {
  id: string;
  name: string;
  icon: string;
  description: string;
  prices: Record<BillingPeriod, number>;
}

export interface InvoiceLine {
  serviceId: string;
  description: string;
  period: BillingPeriod;
  quantity: number;
  unitPrice: number;
}

export interface Invoice {
  id: string;
  number: string;
  clientId: string;
  issuedAt: string;
  dueAt: string;
  status: InvoiceStatus;
  taxRate: number;
  lines: InvoiceLine[];
}
