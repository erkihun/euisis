import { Toaster } from 'sonner';
import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from '@/lib/toast';

interface FlashProps {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
    message?: string | null;
    type?: string | null;
}

/**
 * Drop-in replacement for the legacy ToastProvider.
 *
 * - Renders Sonner's <Toaster> (top-center, rich colours, close button).
 * - Listens for Inertia flash messages and dispatches them via toast.
 */
export default function AppToaster() {
    const page = usePage();
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const flash = (page.props as any).flash as FlashProps | undefined;
    const url = page.url;

    useEffect(() => {
        if (!flash) return;

        if (flash.success)  toast.success(flash.success);
        if (flash.error)    toast.error(flash.error);
        if (flash.warning)  toast.warning(flash.warning);
        if (flash.info)     toast.info(flash.info);

        const hadKeys = Boolean(flash.success || flash.error || flash.warning || flash.info);
        if (!hadKeys && flash.message) {
            const type = flash.type;
            if (type === 'success')       toast.success(flash.message);
            else if (type === 'error')    toast.error(flash.message);
            else if (type === 'warning')  toast.warning(flash.message);
            else                          toast.info(flash.message);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [url]);

    return (
        <Toaster
            position="top-center"
            richColors
            closeButton
            duration={5000}
            toastOptions={{
                classNames: {
                    toast: 'rounded-xl shadow-lg text-sm',
                },
            }}
        />
    );
}
