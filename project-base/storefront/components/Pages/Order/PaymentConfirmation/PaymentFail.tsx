import { ImageWrapper, Message, MessageWrapper, PaymentWrapper } from './PaymentConfirmationElements';
import { Heading } from 'components/Basic/Heading/Heading';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'types/gtm/enums';

export const PaymentFail: FC = () => {
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.payment_fail);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <Webline>
            <MessageWrapper>
                <ImageWrapper>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapper>
                <PaymentWrapper>
                    <Message>
                        <Heading type="h1">{t('Your payment was unsuccessful')}</Heading>
                        <p>{t('We are sorry, but your payment was not successful. Please contact us.')}</p>
                    </Message>
                </PaymentWrapper>
            </MessageWrapper>
        </Webline>
    );
};
