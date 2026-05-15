export type StatusStyle = {
    label: string;
    className: string;
};

const STATUS_MAP: Record<string, StatusStyle> = {
    active: {
        label: 'Active',
        className: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    inactive: {
        label: 'Inactive',
        className: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
    },
    draft: {
        label: 'Draft',
        className: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
    },
    pending: {
        label: 'Pending',
        className: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
    },
    suspended: {
        label: 'Suspended',
        className: 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
    },
    transferred: {
        label: 'Transferred',
        className: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
    },
    retired: {
        label: 'Retired',
        className: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
    },
    verified: {
        label: 'Verified',
        className: 'bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-400',
    },
    approved: {
        label: 'Approved',
        className: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400',
    },
    printed: {
        label: 'Printed',
        className: 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
    },
    issued: {
        label: 'Issued',
        className: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    lost: {
        label: 'Lost',
        className: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    },
    damaged: {
        label: 'Damaged',
        className: 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400',
    },
    replaced: {
        label: 'Replaced',
        className: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
    },
    paused: {
        label: 'Paused',
        className: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
    },
    revoked: {
        label: 'Revoked',
        className: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    },
    expired: {
        label: 'Expired',
        className: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
    },
    exhausted: {
        label: 'Exhausted',
        className: 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
    },
    published: {
        label: 'Published',
        className: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    archived: {
        label: 'Archived',
        className: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
    },
    dissolved: {
        label: 'Dissolved',
        className: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    },
    allowed: {
        label: 'Allowed',
        className: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    denied: {
        label: 'Denied',
        className: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    },
    warning: {
        label: 'Warning',
        className: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
    },
};

const FALLBACK: StatusStyle = {
    label: '',
    className: 'bg-gray-100 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
};

export function getStatusStyle(status: string): StatusStyle {
    const normalized = (status ?? '').toLowerCase().replace(/[\s-]+/g, '_');
    const found = STATUS_MAP[normalized];
    return found
        ? { ...found, label: found.label || status }
        : { ...FALLBACK, label: status };
}
