import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentFailProps = {
    orderPaymentFailedContent: string;
};

export const PaymentFail: FC<PaymentFailProps> = ({ orderPaymentFailedContent }) => {
    const { t } = useTranslation();

    return (
        <ConfirmationPageContent
            content={orderPaymentFailedContent}
            heading={t('Your payment was not successful')}
            headingIcon={WarningIcon}
            headingVariant="error"
        />
    );
};
