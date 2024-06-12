import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

type PaymentSuccessProps = {
    orderUuid: string;
    orderPaymentSuccessfulContent: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderPaymentSuccessfulContent }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_success);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <ConfirmationPageContent content={orderPaymentSuccessfulContent} heading={t('Your payment was successful')} />
    );
};
