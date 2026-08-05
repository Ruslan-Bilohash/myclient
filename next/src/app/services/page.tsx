import { services } from "@/lib/demo-data";
import { formatMoney, formatPeriod } from "@/lib/format";

export const metadata = { title: "Services · My Clients" };

export default function ServicesPage() {
  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Services</h1>
        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Catalog with pricing for 1 / 3 / 12 month periods.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {services.map((service) => (
          <div key={service.id} className="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 className="font-medium text-zinc-900 dark:text-zinc-50">{service.name}</h2>
            <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{service.description}</p>
            <dl className="mt-4 space-y-1 text-sm">
              {Object.entries(service.prices).map(([period, price]) => (
                <div key={period} className="flex justify-between">
                  <dt className="text-zinc-500">{formatPeriod(period)}</dt>
                  <dd className="font-medium text-zinc-900 dark:text-zinc-50">{formatMoney(price)}</dd>
                </div>
              ))}
            </dl>
          </div>
        ))}
      </div>
    </div>
  );
}
