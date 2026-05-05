import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PaymentFailProps = {
    orderPaymentFailedContent: string;
};

export const PaymentFail: FC<PaymentFailProps> = ({ orderPaymentFailedContent }) => {
    const { t } = useTranslation();
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.payment_fail);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <ConfirmationPageContent
            content={orderPaymentFailedContent}
            heading={t('Your payment was not successful')}
            headingClassName="text-text-error"
        />
    );
};
