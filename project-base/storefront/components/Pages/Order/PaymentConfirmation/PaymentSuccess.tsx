import { ImageWrapper, Message, MessageWrapper, PaymentWrapper } from './PaymentConfirmationElements';
import { useOrderSentPageContentApi } from 'graphql/generated';
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

    const [{ data }] = useOrderSentPageContentApi({ variables: { orderUuid } });

    return (
        <MessageWrapper>
            <ImageWrapper>
                <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
            </ImageWrapper>
            <PaymentWrapper>{data !== undefined && <Message message={data.orderSentPageContent} />}</PaymentWrapper>
        </MessageWrapper>
    );
};
