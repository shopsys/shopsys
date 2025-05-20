import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

type PaymentFailProps = {
    orderPaymentFailedContent: string;
};

export const PaymentFail: FC<PaymentFailProps> = ({ orderPaymentFailedContent }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_fail);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <ConfirmationPageContent
            content={orderPaymentFailedContent}
            heading={t('Your payment was not successful')}
            headingClassName="text-text-error"
        />
    );
};
