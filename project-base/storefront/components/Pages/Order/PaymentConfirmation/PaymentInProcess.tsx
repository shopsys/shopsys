import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

type PaymentInProcessProps = {
    orderPaymentInProcessContent: string;
};

export const PaymentInProcess: FC<PaymentInProcessProps> = ({ orderPaymentInProcessContent }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_in_process);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <ConfirmationPageContent content={orderPaymentInProcessContent} heading={t('The payment is being processed')} />
    );
};
