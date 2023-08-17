import { ImageWrapper, Message, MessageWrapper, PaymentWrapper } from './PaymentConfirmationElements';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'gtm/types/enums';
import { useOrderSentPageContentApi } from 'graphql/requests/orders/queries/OrderSentPageContentQuery.generated';

type PaymentSuccessProps = {
    orderUuid: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderUuid }) => {
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_success);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data }] = useOrderSentPageContentApi({ variables: { orderUuid } });

    return (
        <Webline>
            <MessageWrapper>
                <ImageWrapper>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapper>
                <PaymentWrapper>{data !== undefined && <Message message={data.orderSentPageContent} />}</PaymentWrapper>
            </MessageWrapper>
        </Webline>
    );
};
