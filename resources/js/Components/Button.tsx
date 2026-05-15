import { forwardRef, type ButtonHTMLAttributes, type ReactNode } from 'react';

type Variant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'destructive' | 'warning' | 'success';
type Size = 'xs' | 'sm' | 'md' | 'lg';

interface Props extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: Variant;
    size?: Size;
    loading?: boolean;
    icon?: ReactNode;
    iconPosition?: 'left' | 'right';
}

const variantClasses: Record<Variant, string> = {
    primary:
        'bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-500 disabled:bg-blue-300 dark:disabled:bg-blue-900',
    secondary:
        'bg-gray-100 text-gray-700 hover:bg-gray-200 focus-visible:ring-gray-400 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600',
    outline:
        'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus-visible:ring-gray-400 dark:border-slate-600 dark:bg-transparent dark:text-slate-200 dark:hover:bg-slate-800',
    ghost:
        'text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus-visible:ring-gray-400 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
    destructive:
        'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500 disabled:bg-red-300',
    warning:
        'bg-amber-500 text-white hover:bg-amber-600 focus-visible:ring-amber-400 disabled:bg-amber-300',
    success:
        'bg-green-600 text-white hover:bg-green-700 focus-visible:ring-green-500 disabled:bg-green-300',
};

const sizeClasses: Record<Size, string> = {
    xs: 'h-7 px-2.5 text-xs gap-1',
    sm: 'h-8 px-3 text-sm gap-1.5',
    md: 'h-9 px-4 text-sm gap-2',
    lg: 'h-10 px-5 text-base gap-2',
};

const Spinner = () => (
    <svg
        className="h-4 w-4 animate-spin"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        aria-hidden="true"
    >
        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
    </svg>
);

const Button = forwardRef<HTMLButtonElement, Props>(
    (
        {
            variant = 'primary',
            size = 'md',
            loading = false,
            icon,
            iconPosition = 'left',
            children,
            disabled,
            className = '',
            ...rest
        },
        ref,
    ) => {
        const isDisabled = disabled || loading;

        return (
            <button
                ref={ref}
                disabled={isDisabled}
                className={[
                    'inline-flex items-center justify-center rounded-lg font-medium transition-colors',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
                    'disabled:pointer-events-none disabled:opacity-60',
                    variantClasses[variant],
                    sizeClasses[size],
                    className,
                ].join(' ')}
                {...rest}
            >
                {loading && <Spinner />}
                {!loading && icon && iconPosition === 'left' && (
                    <span className="shrink-0" aria-hidden="true">{icon}</span>
                )}
                {children && <span>{children}</span>}
                {!loading && icon && iconPosition === 'right' && (
                    <span className="shrink-0" aria-hidden="true">{icon}</span>
                )}
            </button>
        );
    },
);

Button.displayName = 'Button';
export default Button;
