export function formatMoney(amount: number): string {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(amount);
}

export function formatDate(iso: string): string {
  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(new Date(iso));
}

const periodLabels: Record<string, string> = {
  m1: "1 month",
  m3: "3 months",
  m12: "12 months",
};

export function formatPeriod(period: string): string {
  return periodLabels[period] ?? period;
}
