import Button from '@/Components/Button';
import { useLocale } from '@/hooks/useLocale';

type Props = {
    onClick: () => void;
    disabled?: boolean;
    processing?: boolean;
};

export default function TestChannelButton({ onClick, disabled = false, processing = false }: Props) {
    const { t } = useLocale();

    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={onClick}
            disabled={disabled}
            loading={processing}
        >
            {t('settings.testChannel')}
        </Button>
    );
}
