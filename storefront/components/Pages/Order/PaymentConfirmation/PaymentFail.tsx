import {
    ImageWrapperStyled,
    MessageStyled,
    MessageWrapperStyled,
    PaymentWrapperStyled,
} from './PaymentConfirmation.style';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const PaymentFail: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <Webline>
            <MessageWrapperStyled>
                <ImageWrapperStyled>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <PaymentWrapperStyled>
                    <MessageStyled>
                        <Heading type="h1">{t('Your payment was not processed')}</Heading>
                        <p>{t('Try it again')}</p>
                    </MessageStyled>
                </PaymentWrapperStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};

export default PaymentFail;
