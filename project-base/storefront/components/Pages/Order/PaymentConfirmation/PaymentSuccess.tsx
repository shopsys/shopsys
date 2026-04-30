import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentSuccessProps = {
    orderPaymentSuccessfulContent: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderPaymentSuccessfulContent }) => {
    const { t } = useTranslation();
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.payment_success);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <ConfirmationPageContent content={orderPaymentSuccessfulContent} heading={t('Your payment was successful')} />
    );
};
