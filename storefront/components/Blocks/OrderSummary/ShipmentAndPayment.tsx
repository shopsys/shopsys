import {
    ImgStyled,
    OrderSummaryContent,
    ShipmentAndPaymentImageWrapper,
    ShipmentAndPaymentPrice,
    ShipmentAndPaymentTextAndImage,
    ShipmentAndPaymentWrapper,
    SummaryRow,
} from './OrderSummary.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ShipmentAndPayment: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <ShipmentAndPaymentWrapper>
            {/* TODO PRG: shipment and payment is hard coded */}
            <OrderSummaryContent>
                <SummaryRow>
                    <ShipmentAndPaymentTextAndImage>
                        PPL
                        <ShipmentAndPaymentImageWrapper>
                            <ImgStyled src="https://master.ssfwcc.ci.shopsys.cloud/content/images/transport/default/57.jpg" />
                        </ShipmentAndPaymentImageWrapper>
                    </ShipmentAndPaymentTextAndImage>
                    <ShipmentAndPaymentPrice>
                        <strong>{formatPrice(242, 'CZK', t)}</strong>
                    </ShipmentAndPaymentPrice>
                </SummaryRow>
                <SummaryRow>
                    <ShipmentAndPaymentTextAndImage>
                        {t('By credit card')}
                        <ShipmentAndPaymentImageWrapper>
                            <ImgStyled src="https://master.ssfwcc.ci.shopsys.cloud/content/images/payment/default/53.jpg" />
                        </ShipmentAndPaymentImageWrapper>
                    </ShipmentAndPaymentTextAndImage>
                    <ShipmentAndPaymentPrice>
                        <strong>{formatPrice(100, 'CZK', t)}</strong>
                    </ShipmentAndPaymentPrice>
                </SummaryRow>
            </OrderSummaryContent>
        </ShipmentAndPaymentWrapper>
    );
};

export default ShipmentAndPayment;
