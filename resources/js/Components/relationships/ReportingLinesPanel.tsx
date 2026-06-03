import { useLocale } from '@/hooks/useLocale';
import type { RelationshipRow } from './RelationshipPanel';

type Props = {
    rows: RelationshipRow[];
};

export default function ReportingLinesPanel({ rows }: Props) {
    const { t } = useLocale();

    return (
        <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <h3 className="font-semibold text-gray-900 dark:text-slate-100">{t('relationships.reportingLines')}</h3>
            <div className="mt-4 space-y-2">
                {rows.length === 0 ? (
                    <p className="text-sm text-gray-400 dark:text-slate-500">{t('relationships.noRelationships')}</p>
                ) : rows.map((row) => (
                    <div key={row.id} className="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-100 px-3 py-2 text-sm dark:border-slate-800">
                        <span className="font-medium text-gray-800 dark:text-slate-200">{row.relationship_label}</span>
                        <span className="text-gray-500 dark:text-slate-400">{row.target?.name_en ?? row.target_id}</span>
                    </div>
                ))}
            </div>
        </section>
    );
}
