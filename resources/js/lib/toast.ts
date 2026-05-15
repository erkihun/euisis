export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface ToastItem {
    id: string;
    type: ToastType;
    message: string;
}

type Listener = (toasts: ToastItem[]) => void;

let toasts: ToastItem[] = [];
const listeners: Set<Listener> = new Set();

function notify() {
    listeners.forEach((fn) => fn([...toasts]));
}

function add(type: ToastType, message: string): string {
    const id = `toast-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
    toasts = [...toasts, { id, type, message }];
    notify();
    return id;
}

function dismiss(id: string) {
    toasts = toasts.filter((t) => t.id !== id);
    notify();
}

export function subscribeToToasts(fn: Listener): () => void {
    listeners.add(fn);
    fn([...toasts]);
    return () => { listeners.delete(fn); };
}

export const toast = {
    success: (message: string) => add('success', message),
    error: (message: string) => add('error', message),
    warning: (message: string) => add('warning', message),
    info: (message: string) => add('info', message),
    dismiss,
};
