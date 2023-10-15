import { GoPayGateway } from './Gateways/GoPayGateway';
import { ImageWrapper, Message, MessageWrapper, PaymentWrapper } from './PaymentConfirmationElements';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type PaymentFailProps = {
    orderUuid: string;
    orderPaymentType: string | undefined;
    canPaymentBeRepeated: boolean;
};

export const PaymentFail: FC<PaymentFailProps> = ({ orderUuid, orderPaymentType, canPaymentBeRepeated }) => {
    const { t } = useTranslation();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_fail);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <MessageWrapper>
            <ImageWrapper>
                <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
            </ImageWrapper>
            <PaymentWrapper>
                <Message>
                    <div className="h1 mb-3">{t('Your payment was unsuccessful')}</div>

                    {canPaymentBeRepeated && orderPaymentType === PaymentTypeEnum.GoPay ? (
                        <>
                            <p>
                                {t(
                                    'We are sorry, but your payment was not successful. You can try repeating it. Otherwise, you can contact us.',
                                )}
                            </p>
                            <GoPayGateway
                                requiresAction
                                initialButtonText={t('Repeat payment')}
                                orderUuid={orderUuid}
                            />
                        </>
                    ) : (
                        <p>{t('We are sorry, but your payment was not successful. Please contact us.')}</p>
                    )}
                </Message>
            </PaymentWrapper>
        </MessageWrapper>
    );
};
