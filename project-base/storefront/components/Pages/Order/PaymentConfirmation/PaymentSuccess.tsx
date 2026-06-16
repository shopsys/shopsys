import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentSuccessProps = {
    orderPaymentSuccessfulContent: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderPaymentSuccessfulContent }) => {
    const { t } = useTranslation();

    return (
        <ConfirmationPageContent
            content={orderPaymentSuccessfulContent}
            heading={t('Your payment was successful')}
            headingIcon={CheckmarkIcon}
            headingVariant="success"
        />
    );
};
