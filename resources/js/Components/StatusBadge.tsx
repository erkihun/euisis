import { getStatusStyle } from '@/lib/statusStyles';

interface Props {
    status: string;
    label?: string;
    className?: string;
}

export default function StatusBadge({ status, label, className = '' }: Props) {
    const style = getStatusStyle(status);
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${style.className} ${className}`}
        >
            {label ?? style.label}
        </span>
    );
}
