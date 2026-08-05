import { Alert } from 'components/Basic/Alert/Alert';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const NoAvailablePaymentInfo: FC = () => {
    const { t } = useTranslation();

    return (
        <Alert
            icon={WarningIcon}
            tid={TIDs.pages_order_no_available_payment_info}
            title={t('No suitable payment method is available')}
            variant="warning"
        >
            <span className="text-text-less">
                {t(
                    'We are sorry, we could not find a suitable payment method for this type of goods. Please contact us if you need to complete the order.',
                )}
            </span>
        </Alert>
    );
};
