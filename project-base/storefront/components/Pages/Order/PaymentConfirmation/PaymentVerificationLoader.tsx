import { LockIcon } from 'components/Basic/Icon/LockIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentVerificationLoaderProps = {
    heading?: string;
    subtitle?: string;
};

export const PaymentVerificationLoader: FC<PaymentVerificationLoaderProps> = ({ heading, subtitle }) => {
    const { t } = useTranslation();

    return (
        <div className="fixed inset-0 z-overlay flex items-center justify-center bg-overlay-default px-5">
            <div className="flex w-full max-w-screen-md flex-col items-center gap-4 rounded-md bg-background-default px-8 py-6 shadow-2xl">
                <div className="flex size-16 items-center justify-center rounded-full bg-background-most">
                    <LockIcon className="size-7" />
                </div>

                <h3 className="text-center">{heading ?? t('Checking your payment status...')}</h3>

                <p className="text-center text-text-disabled">
                    {subtitle ?? t('We are verifying the latest payment result. This will only take a moment.')}
                </p>

                <SpinnerIcon className="size-10" />

                <span className="font-semibold text-sm text-text-accent uppercase tracking-widest">
                    {t('Secure transaction')}
                </span>
            </div>
        </div>
    );
};
