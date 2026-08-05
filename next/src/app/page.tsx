import Link from "next/link";
import { StatCard } from "@/components/StatCard";
import { StatusBadge } from "@/components/StatusBadge";
import { clients, getClient, invoiceTotal, invoices } from "@/lib/demo-data";
import { formatDate, formatMoney } from "@/lib/format";

export default function Home() {
  const activeClients = clients.filter((c) => c.status === "active").length;
  const paidTotal = invoices
    .filter((i) => i.status === "paid")
    .reduce((sum, i) => sum + invoiceTotal(i), 0);
  const pendingTotal = invoices
    .filter((i) => i.status === "pending")
    .reduce((sum, i) => sum + invoiceTotal(i), 0);
  const recentInvoices = [...invoices]
    .sort((a, b) => (a.issuedAt < b.issuedAt ? 1 : -1))
    .slice(0, 5);

  return (
    <div className="flex flex-col gap-8">
      <div>
        <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Dashboard</h1>
        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
          Demo data — clients, service catalog and invoices.
        </p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard label="Active clients" value={String(activeClients)} hint={`${clients.length} total`} />
        <StatCard label="Paid revenue" value={formatMoney(paidTotal)} />
        <StatCard label="Pending invoices" value={formatMoney(pendingTotal)} />
        <StatCard label="Services in catalog" value={String(5)} />
      </div>

      <div className="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div className="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
          <h2 className="font-medium text-zinc-900 dark:text-zinc-50">Recent invoices</h2>
          <Link href="/invoices" className="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50">
            View all
          </Link>
        </div>
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs uppercase text-zinc-400">
              <th className="px-5 py-3 font-medium">Number</th>
              <th className="px-5 py-3 font-medium">Client</th>
              <th className="px-5 py-3 font-medium">Issued</th>
              <th className="px-5 py-3 font-medium">Status</th>
              <th className="px-5 py-3 text-right font-medium">Total</th>
            </tr>
          </thead>
          <tbody>
            {recentInvoices.map((invoice) => {
              const client = getClient(invoice.clientId);
              return (
                <tr key={invoice.id} className="border-t border-zinc-100 dark:border-zinc-800">
                  <td className="px-5 py-3">
                    <Link href={`/invoices/${invoice.id}`} className="font-medium text-zinc-900 hover:underline dark:text-zinc-50">
                      {invoice.number}
                    </Link>
                  </td>
                  <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{client?.company}</td>
                  <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{formatDate(invoice.issuedAt)}</td>
                  <td className="px-5 py-3">
                    <StatusBadge status={invoice.status} />
                  </td>
                  <td className="px-5 py-3 text-right font-medium text-zinc-900 dark:text-zinc-50">
                    {formatMoney(invoiceTotal(invoice))}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}
