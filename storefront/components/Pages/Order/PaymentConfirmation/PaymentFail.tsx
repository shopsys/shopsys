import {
    ImageWrapperStyled,
    MessageStyled,
    MessageWrapperStyled,
    PaymentWrapperStyled,
} from './PaymentConfirmation.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

export const PaymentFail: FC = () => {
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('purchase fail');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <Webline>
            <MessageWrapperStyled>
                <ImageWrapperStyled>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <PaymentWrapperStyled>
                    <MessageStyled>
                        <Heading type="h1">{t('Your payment was unsuccessful')}</Heading>
                        <p>{t('We are sorry, but your payment was not successful. Please contact us.')}</p>
                    </MessageStyled>
                </PaymentWrapperStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};
