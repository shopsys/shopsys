import {
    ImageWrapperStyled,
    MessageStyled,
    MessageWrapperStyled,
    PaymentEmailStyled,
    PaymentWrapperStyled,
} from './PaymentConfirmation.style';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const PaymentSuccess: FC = () => {
    const t = useTypedTranslationFunction();
    const { email } = useCurrentUserContactInformation();

    return (
        <Webline>
            <MessageWrapperStyled>
                <ImageWrapperStyled>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <PaymentWrapperStyled>
                    <MessageStyled>
                        <Heading type="h1">{t('Thank you for your order, your payment was successful')}</Heading>
                        <p>{t('We have sent a recap of your order to your email')}</p>
                        <PaymentEmailStyled>{email}</PaymentEmailStyled>
                    </MessageStyled>
                </PaymentWrapperStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};

export default PaymentSuccess;
