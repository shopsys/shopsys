import { ImageWrapper, Message, MessageWrapper, PaymentWrapper } from './PaymentConfirmationElements';
import { Heading } from 'components/Basic/Heading/Heading';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

export const PaymentFail: FC = () => {
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('purchase fail');
    useGtmStaticPageView(gtmStaticPageViewEvent);

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
