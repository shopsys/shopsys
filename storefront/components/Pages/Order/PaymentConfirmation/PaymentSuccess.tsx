import {
    ImageWrapperStyled,
    MessageStyled,
    MessageWrapperStyled,
    PaymentEmailStyled,
    PaymentWrapperStyled,
} from './PaymentConfirmation.style';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const PaymentSuccess: FC = () => {
    const t = useTypedTranslationFunction();
    const { email } = useShopsysSelector((state) => state.contactInformation);

    return (
        <Webline>
            <MessageWrapperStyled>
                <ImageWrapperStyled>
                    <img alt={t('Order sent')} src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <PaymentWrapperStyled>
                    <MessageStyled>
                        <Heading type="h1">{t('Děkujeme za vaší objednávku, vaše platba proběhla úspěšně')}</Heading>
                        <p>{t('Odeslali jsme na váš e-mail rekapitulaci objednávky.')}</p>
                        <PaymentEmailStyled>{email}</PaymentEmailStyled>
                    </MessageStyled>
                </PaymentWrapperStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};

export default PaymentSuccess;
