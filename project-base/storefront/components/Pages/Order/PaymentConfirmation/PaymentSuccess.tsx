import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { useOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

type PaymentSuccessProps = {
    orderUuid: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderUuid }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_success);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: contentData, fetching }] = useOrderPaymentSuccessfulContentQuery({ variables: { orderUuid } });

    return (
        <ConfirmationPageContent
            content={contentData?.orderPaymentSuccessfulContent}
            heading={t('Your payment was successful')}
            isFetching={fetching}
        />
    );
};
