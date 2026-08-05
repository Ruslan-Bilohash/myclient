import type { ClientStatus, InvoiceStatus } from "@/lib/types";

const styles: Record<string, string> = {
  paid: "bg-emerald-50 text-emerald-700 ring-emerald-600/20",
  active: "bg-emerald-50 text-emerald-700 ring-emerald-600/20",
  pending: "bg-amber-50 text-amber-700 ring-amber-600/20",
  cancelled: "bg-rose-50 text-rose-700 ring-rose-600/20",
  inactive: "bg-zinc-100 text-zinc-600 ring-zinc-500/20",
};

export function StatusBadge({ status }: { status: InvoiceStatus | ClientStatus }) {
  return (
    <span
      className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ring-1 ring-inset ${styles[status]}`}
    >
      {status}
    </span>
  );
}
