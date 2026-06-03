interface Props {
    title?: string;
    compact?: boolean;
}

export default function EmptyDashboardState({ title, compact = false }: Props) {
    return (
        <div className={`flex items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 text-center dark:border-slate-700 dark:bg-slate-950/40 ${compact ? 'min-h-[160px] p-6' : 'min-h-[280px] p-10'}`}>
            <p className="text-sm text-gray-500 dark:text-slate-400">{title}</p>
        </div>
    );
}
