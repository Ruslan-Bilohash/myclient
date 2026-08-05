import Link from "next/link";
import { notFound } from "next/navigation";
import { StatusBadge } from "@/components/StatusBadge";
import { getClient, invoicesForClient, invoiceTotal } from "@/lib/demo-data";
import { formatDate, formatMoney } from "@/lib/format";

export default async function ClientPage(props: PageProps<"/clients/[id]">) {
  const { id } = await props.params;
  const client = getClient(id);
  if (!client) notFound();

  const clientInvoices = invoicesForClient(client.id);

  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">{client.name}</h1>
          <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{client.company}</p>
        </div>
        <StatusBadge status={client.status} />
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div className="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h2 className="mb-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact</h2>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
              <dt className="text-zinc-500">Email</dt>
              <dd className="text-zinc-900 dark:text-zinc-50">{client.email}</dd>
            </div>
            {client.site && (
              <div className="flex justify-between">
                <dt className="text-zinc-500">Site</dt>
                <dd className="text-zinc-900 dark:text-zinc-50">{client.site}</dd>
              </div>
            )}
            <div className="flex justify-between">
              <dt className="text-zinc-500">Language</dt>
              <dd className="uppercase text-zinc-900 dark:text-zinc-50">{client.language}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-zinc-500">Client since</dt>
              <dd className="text-zinc-900 dark:text-zinc-50">{formatDate(client.createdAt)}</dd>
            </div>
          </dl>
        </div>
        {client.notes && (
          <div className="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 className="mb-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Notes</h2>
            <p className="text-sm text-zinc-700 dark:text-zinc-300">{client.notes}</p>
          </div>
        )}
      </div>

      <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div className="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
          <h2 className="font-medium text-zinc-900 dark:text-zinc-50">Invoices</h2>
        </div>
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs uppercase text-zinc-400">
              <th className="px-5 py-3 font-medium">Number</th>
              <th className="px-5 py-3 font-medium">Issued</th>
              <th className="px-5 py-3 font-medium">Status</th>
              <th className="px-5 py-3 text-right font-medium">Total</th>
            </tr>
          </thead>
          <tbody>
            {clientInvoices.map((invoice) => (
              <tr key={invoice.id} className="border-t border-zinc-100 dark:border-zinc-800">
                <td className="px-5 py-3">
                  <Link href={`/invoices/${invoice.id}`} className="font-medium text-zinc-900 hover:underline dark:text-zinc-50">
                    {invoice.number}
                  </Link>
                </td>
                <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{formatDate(invoice.issuedAt)}</td>
                <td className="px-5 py-3">
                  <StatusBadge status={invoice.status} />
                </td>
                <td className="px-5 py-3 text-right font-medium text-zinc-900 dark:text-zinc-50">
                  {formatMoney(invoiceTotal(invoice))}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
