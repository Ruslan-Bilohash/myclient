import Link from "next/link";
import { notFound } from "next/navigation";
import { StatusBadge } from "@/components/StatusBadge";
import {
  getClient,
  getInvoice,
  invoiceLineTotal,
  invoiceSubtotal,
  invoiceTax,
  invoiceTotal,
} from "@/lib/demo-data";
import { formatDate, formatMoney, formatPeriod } from "@/lib/format";

export default async function InvoicePage(props: PageProps<"/invoices/[id]">) {
  const { id } = await props.params;
  const invoice = getInvoice(id);
  if (!invoice) notFound();

  const client = getClient(invoice.clientId);

  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">{invoice.number}</h1>
          {client && (
            <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
              Billed to{" "}
              <Link href={`/clients/${client.id}`} className="font-medium hover:underline">
                {client.company}
              </Link>
            </p>
          )}
        </div>
        <StatusBadge status={invoice.status} />
      </div>

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div className="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p className="text-xs text-zinc-500">Issued</p>
          <p className="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-50">{formatDate(invoice.issuedAt)}</p>
        </div>
        <div className="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p className="text-xs text-zinc-500">Due</p>
          <p className="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-50">{formatDate(invoice.dueAt)}</p>
        </div>
        <div className="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p className="text-xs text-zinc-500">Tax rate</p>
          <p className="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-50">{invoice.taxRate}%</p>
        </div>
        <div className="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p className="text-xs text-zinc-500">Total</p>
          <p className="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-50">{formatMoney(invoiceTotal(invoice))}</p>
        </div>
      </div>

      <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs uppercase text-zinc-400">
              <th className="px-5 py-3 font-medium">Description</th>
              <th className="px-5 py-3 font-medium">Period</th>
              <th className="px-5 py-3 text-right font-medium">Qty</th>
              <th className="px-5 py-3 text-right font-medium">Unit price</th>
              <th className="px-5 py-3 text-right font-medium">Total</th>
            </tr>
          </thead>
          <tbody>
            {invoice.lines.map((line, index) => (
              <tr key={`${line.serviceId}-${index}`} className="border-t border-zinc-100 dark:border-zinc-800">
                <td className="px-5 py-3 text-zinc-900 dark:text-zinc-50">{line.description}</td>
                <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{formatPeriod(line.period)}</td>
                <td className="px-5 py-3 text-right text-zinc-600 dark:text-zinc-400">{line.quantity}</td>
                <td className="px-5 py-3 text-right text-zinc-600 dark:text-zinc-400">{formatMoney(line.unitPrice)}</td>
                <td className="px-5 py-3 text-right font-medium text-zinc-900 dark:text-zinc-50">
                  {formatMoney(invoiceLineTotal(line))}
                </td>
              </tr>
            ))}
          </tbody>
          <tfoot>
            <tr className="border-t border-zinc-200 dark:border-zinc-800">
              <td colSpan={4} className="px-5 py-2 text-right text-zinc-500">Subtotal</td>
              <td className="px-5 py-2 text-right text-zinc-900 dark:text-zinc-50">{formatMoney(invoiceSubtotal(invoice))}</td>
            </tr>
            <tr>
              <td colSpan={4} className="px-5 py-2 text-right text-zinc-500">Tax ({invoice.taxRate}%)</td>
              <td className="px-5 py-2 text-right text-zinc-900 dark:text-zinc-50">{formatMoney(invoiceTax(invoice))}</td>
            </tr>
            <tr>
              <td colSpan={4} className="px-5 py-3 text-right font-medium text-zinc-900 dark:text-zinc-50">Total</td>
              <td className="px-5 py-3 text-right font-semibold text-zinc-900 dark:text-zinc-50">{formatMoney(invoiceTotal(invoice))}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  );
}
