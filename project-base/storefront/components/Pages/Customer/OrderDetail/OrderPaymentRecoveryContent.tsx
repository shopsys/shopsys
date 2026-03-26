import { SkeletonPageConfirmation } from 'components/Blocks/Skeleton/SkeletonPageConfirmation';
import { PaymentVerificationLoader } from 'components/Pages/Order/PaymentConfirmation/PaymentVerificationLoader';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const OrderPaymentRecoveryContent: FC = () => {
    const { t } = useTranslation();

    return (
        <div className="relative">
            <PaymentVerificationLoader
                heading={t('Checking your payment status...')}
                subtitle={t('We are restoring your payment session and verifying the latest payment result.')}
            />

            <SkeletonPageConfirmation />
        </div>
    );
};
