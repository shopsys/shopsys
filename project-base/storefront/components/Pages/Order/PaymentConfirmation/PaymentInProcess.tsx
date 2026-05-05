import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentInProcessProps = {
    orderPaymentInProcessContent: string;
};

export const PaymentInProcess: FC<PaymentInProcessProps> = ({ orderPaymentInProcessContent }) => {
    const { t } = useTranslation();
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.payment_in_process);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <ConfirmationPageContent content={orderPaymentInProcessContent} heading={t('The payment is being processed')} />
    );
};
