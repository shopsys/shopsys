import { ImageWrapperStyled, MessageStyled, MessageWrapperStyled, OrderEmailStyled } from './OrderConfirmation.style';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const OrderConfirmation: FC = () => {
    const t = useTypedTranslationFunction();
    const { email } = useShopsysSelector((state) => state.user);

    return (
        <Webline>
            <MessageWrapperStyled>
                <ImageWrapperStyled>
                    <img alt="Objednávka odeslána" src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <MessageStyled>
                    <Heading type="h1">{t('Thank you for your order')}</Heading>
                    <p>{t('We have also sent a summary to your email')}</p>
                    <OrderEmailStyled>{email}</OrderEmailStyled>
                </MessageStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};

export default OrderConfirmation;
