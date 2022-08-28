import {
    ImageWrapperStyled,
    MessageStyled,
    MessageWrapperStyled,
    PaymentWrapperStyled,
} from './PaymentConfirmation.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { useOrderSentPageContentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';

type PaymentSuccessProps = {
    orderUuid: string;
};

export const PaymentSuccess: FC<PaymentSuccessProps> = ({ orderUuid }) => {
    const t = useTypedTranslationFunction();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('purchase success');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    const [{ data }] = useOrderSentPageContentApi({ variables: { orderUuid } });

    return (
        <Webline>
            <MessageWrapperStyled>
                <ImageWrapperStyled>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <PaymentWrapperStyled>
                    {data !== undefined && (
                        <MessageStyled dangerouslySetInnerHTML={{ __html: data.orderSentPageContent }} />
                    )}
                </PaymentWrapperStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};
