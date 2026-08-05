import Link from "next/link";
import { StatusBadge } from "@/components/StatusBadge";
import { getClient, invoiceTotal, invoices } from "@/lib/demo-data";
import { formatDate, formatMoney } from "@/lib/format";

export const metadata = { title: "Invoices · My Clients" };

export default function InvoicesPage() {
  const sorted = [...invoices].sort((a, b) => (a.issuedAt < b.issuedAt ? 1 : -1));

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Invoices</h1>
        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{invoices.length} invoices in the demo dataset.</p>
      </div>

      <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs uppercase text-zinc-400">
              <th className="px-5 py-3 font-medium">Number</th>
              <th className="px-5 py-3 font-medium">Client</th>
              <th className="px-5 py-3 font-medium">Issued</th>
              <th className="px-5 py-3 font-medium">Due</th>
              <th className="px-5 py-3 font-medium">Status</th>
              <th className="px-5 py-3 text-right font-medium">Total</th>
            </tr>
          </thead>
          <tbody>
            {sorted.map((invoice) => {
              const client = getClient(invoice.clientId);
              return (
                <tr key={invoice.id} className="border-t border-zinc-100 dark:border-zinc-800">
                  <td className="px-5 py-3">
                    <Link href={`/invoices/${invoice.id}`} className="font-medium text-zinc-900 hover:underline dark:text-zinc-50">
                      {invoice.number}
                    </Link>
                  </td>
                  <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">
                    <Link href={`/clients/${invoice.clientId}`} className="hover:underline">
                      {client?.company}
                    </Link>
                  </td>
                  <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{formatDate(invoice.issuedAt)}</td>
                  <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{formatDate(invoice.dueAt)}</td>
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
