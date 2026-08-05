import Link from "next/link";
import { StatusBadge } from "@/components/StatusBadge";
import { clients } from "@/lib/demo-data";
import { formatDate } from "@/lib/format";

export const metadata = { title: "Clients · My Clients" };

export default function ClientsPage() {
  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Clients</h1>
        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{clients.length} clients in the demo dataset.</p>
      </div>

      <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs uppercase text-zinc-400">
              <th className="px-5 py-3 font-medium">Name</th>
              <th className="px-5 py-3 font-medium">Company</th>
              <th className="px-5 py-3 font-medium">Email</th>
              <th className="px-5 py-3 font-medium">Since</th>
              <th className="px-5 py-3 font-medium">Status</th>
            </tr>
          </thead>
          <tbody>
            {clients.map((client) => (
              <tr key={client.id} className="border-t border-zinc-100 dark:border-zinc-800">
                <td className="px-5 py-3">
                  <Link href={`/clients/${client.id}`} className="font-medium text-zinc-900 hover:underline dark:text-zinc-50">
                    {client.name}
                  </Link>
                </td>
                <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{client.company}</td>
                <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{client.email}</td>
                <td className="px-5 py-3 text-zinc-600 dark:text-zinc-400">{formatDate(client.createdAt)}</td>
                <td className="px-5 py-3">
                  <StatusBadge status={client.status} />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
