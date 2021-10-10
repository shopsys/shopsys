import {
    ImgStyled,
    OrderSummaryContent,
    SummaryRow,
    TransportAndPaymentImageWrapper,
    TransportAndPaymentPrice,
    TransportAndPaymentTextAndImage,
    TransportAndPaymentWrapper,
} from './OrderSummary.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const TransportAndPayment: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <TransportAndPaymentWrapper>
            <OrderSummaryContent>
                <SummaryRow>
                    <TransportAndPaymentTextAndImage>
                        PPL
                        <TransportAndPaymentImageWrapper>
                            <ImgStyled src="https://master.ssfwcc.ci.shopsys.cloud/content/images/transport/default/57.jpg" />
                        </TransportAndPaymentImageWrapper>
                    </TransportAndPaymentTextAndImage>
                    <TransportAndPaymentPrice>
                        <strong>{formatPrice(242, 'CZK', t)}</strong>
                    </TransportAndPaymentPrice>
                </SummaryRow>
                <SummaryRow>
                    <TransportAndPaymentTextAndImage>
                        {t('By credit card')}
                        <TransportAndPaymentImageWrapper>
                            <ImgStyled src="https://master.ssfwcc.ci.shopsys.cloud/content/images/payment/default/53.jpg" />
                        </TransportAndPaymentImageWrapper>
                    </TransportAndPaymentTextAndImage>
                    <TransportAndPaymentPrice>
                        <strong>{formatPrice(100, 'CZK', t)}</strong>
                    </TransportAndPaymentPrice>
                </SummaryRow>
            </OrderSummaryContent>
        </TransportAndPaymentWrapper>
    );
};

export default TransportAndPayment;
