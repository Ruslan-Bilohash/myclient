import type { Client, Invoice, Service } from "./types";

export const clients: Client[] = [
  {
    id: "c1",
    name: "Anna Berg",
    company: "Berg Design Studio",
    email: "anna.berg@demo.myclient",
    site: "bergdesign.no",
    language: "no",
    status: "active",
    notes: "Prefers invoices sent on the 1st of the month.",
    createdAt: "2025-02-11",
  },
  {
    id: "c2",
    name: "Oleh Petrenko",
    company: "Petrenko Logistics",
    email: "oleh.petrenko@demo.myclient",
    site: "petrenko-log.ua",
    language: "uk",
    status: "active",
    createdAt: "2025-03-04",
  },
  {
    id: "c3",
    name: "Irina Sokolova",
    company: "Sokolova Consulting",
    email: "irina.sokolova@demo.myclient",
    site: "sokolova.ru",
    language: "ru",
    status: "active",
    createdAt: "2025-04-18",
  },
  {
    id: "c4",
    name: "Tom Fredriksen",
    company: "Fredriksen & Co",
    email: "tom.fredriksen@demo.myclient",
    site: "fredriksenco.no",
    language: "no",
    status: "inactive",
    notes: "Paused subscription, revisit in Q3.",
    createdAt: "2025-01-22",
  },
  {
    id: "c5",
    name: "Emily Carter",
    company: "Carter & Partners",
    email: "emily.carter@demo.myclient",
    site: "carterpartners.com",
    language: "en",
    status: "active",
    createdAt: "2025-05-30",
  },
  {
    id: "c6",
    name: "Maksym Kravets",
    company: "Kravets Studio",
    email: "maksym.kravets@demo.myclient",
    site: "kravets.studio",
    language: "uk",
    status: "active",
    createdAt: "2025-06-09",
  },
];

export const services: Service[] = [
  {
    id: "s1",
    name: "Website hosting",
    icon: "server",
    description: "Managed hosting with daily backups and SSL.",
    prices: { m1: 15, m3: 40, m12: 140 },
  },
  {
    id: "s2",
    name: "Website maintenance",
    icon: "wrench",
    description: "Monthly updates, monitoring and small fixes.",
    prices: { m1: 45, m3: 120, m12: 420 },
  },
  {
    id: "s3",
    name: "SEO optimization",
    icon: "trending-up",
    description: "Ongoing on-page SEO and monthly reporting.",
    prices: { m1: 90, m3: 250, m12: 900 },
  },
  {
    id: "s4",
    name: "Email support",
    icon: "mail",
    description: "Mailbox hosting with spam filtering.",
    prices: { m1: 8, m3: 22, m12: 80 },
  },
  {
    id: "s5",
    name: "Domain registration",
    icon: "globe",
    description: "Domain renewal and DNS management.",
    prices: { m1: 5, m3: 14, m12: 45 },
  },
];

export const invoices: Invoice[] = [
  {
    id: "i1",
    number: "INV-2026-0001",
    clientId: "c1",
    issuedAt: "2026-07-01",
    dueAt: "2026-07-15",
    status: "paid",
    taxRate: 25,
    lines: [
      { serviceId: "s1", description: "Website hosting", period: "m1", quantity: 1, unitPrice: 15 },
      { serviceId: "s2", description: "Website maintenance", period: "m1", quantity: 1, unitPrice: 45 },
    ],
  },
  {
    id: "i2",
    number: "INV-2026-0002",
    clientId: "c2",
    issuedAt: "2026-07-03",
    dueAt: "2026-07-17",
    status: "pending",
    taxRate: 20,
    lines: [
      { serviceId: "s3", description: "SEO optimization", period: "m3", quantity: 1, unitPrice: 250 },
    ],
  },
  {
    id: "i3",
    number: "INV-2026-0003",
    clientId: "c3",
    issuedAt: "2026-06-20",
    dueAt: "2026-07-04",
    status: "paid",
    taxRate: 20,
    lines: [
      { serviceId: "s1", description: "Website hosting", period: "m12", quantity: 1, unitPrice: 140 },
      { serviceId: "s5", description: "Domain registration", period: "m12", quantity: 2, unitPrice: 45 },
    ],
  },
  {
    id: "i4",
    number: "INV-2026-0004",
    clientId: "c4",
    issuedAt: "2026-05-11",
    dueAt: "2026-05-25",
    status: "cancelled",
    taxRate: 25,
    lines: [
      { serviceId: "s2", description: "Website maintenance", period: "m1", quantity: 1, unitPrice: 45 },
    ],
  },
  {
    id: "i5",
    number: "INV-2026-0005",
    clientId: "c5",
    issuedAt: "2026-07-20",
    dueAt: "2026-08-03",
    status: "pending",
    taxRate: 0,
    lines: [
      { serviceId: "s4", description: "Email support", period: "m3", quantity: 1, unitPrice: 22 },
      { serviceId: "s1", description: "Website hosting", period: "m3", quantity: 1, unitPrice: 40 },
    ],
  },
  {
    id: "i6",
    number: "INV-2026-0006",
    clientId: "c6",
    issuedAt: "2026-07-28",
    dueAt: "2026-08-11",
    status: "pending",
    taxRate: 20,
    lines: [
      { serviceId: "s3", description: "SEO optimization", period: "m1", quantity: 1, unitPrice: 90 },
      { serviceId: "s4", description: "Email support", period: "m1", quantity: 1, unitPrice: 8 },
    ],
  },
  {
    id: "i7",
    number: "INV-2026-0007",
    clientId: "c1",
    issuedAt: "2026-08-01",
    dueAt: "2026-08-15",
    status: "pending",
    taxRate: 25,
    lines: [
      { serviceId: "s1", description: "Website hosting", period: "m1", quantity: 1, unitPrice: 15 },
    ],
  },
  {
    id: "i8",
    number: "INV-2026-0008",
    clientId: "c3",
    issuedAt: "2026-04-02",
    dueAt: "2026-04-16",
    status: "paid",
    taxRate: 20,
    lines: [
      { serviceId: "s2", description: "Website maintenance", period: "m3", quantity: 1, unitPrice: 120 },
    ],
  },
];

export function getClient(id: string): Client | undefined {
  return clients.find((c) => c.id === id);
}

export function getService(id: string): Service | undefined {
  return services.find((s) => s.id === id);
}

export function getInvoice(id: string): Invoice | undefined {
  return invoices.find((i) => i.id === id);
}

export function invoicesForClient(clientId: string): Invoice[] {
  return invoices.filter((i) => i.clientId === clientId);
}

export function invoiceLineTotal(line: { quantity: number; unitPrice: number }): number {
  return line.quantity * line.unitPrice;
}

export function invoiceSubtotal(invoice: Invoice): number {
  return invoice.lines.reduce((sum, line) => sum + invoiceLineTotal(line), 0);
}

export function invoiceTax(invoice: Invoice): number {
  return (invoiceSubtotal(invoice) * invoice.taxRate) / 100;
}

export function invoiceTotal(invoice: Invoice): number {
  return invoiceSubtotal(invoice) + invoiceTax(invoice);
}
