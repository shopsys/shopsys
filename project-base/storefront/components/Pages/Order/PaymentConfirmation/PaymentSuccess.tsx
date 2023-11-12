import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { useOrderPaymentSuccessfulContentQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';

type PaymentSuccessProps = {
    orderUuid: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderUuid }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_success);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: contentData, fetching }] = useOrderPaymentSuccessfulContentQueryApi({ variables: { orderUuid } });

    return (
        <ConfirmationPageContent
            content={contentData?.orderPaymentSuccessfulContent}
            heading={t('Your payment was successful')}
            isFetching={fetching}
        />
    );
};
