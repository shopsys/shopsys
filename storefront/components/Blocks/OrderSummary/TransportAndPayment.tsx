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
import { PaymentType } from 'connectors/payments/types';
import { TransportType } from 'connectors/transports/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type TransportAndPaymentProps = {
    transport: TransportType | null;
    payment: PaymentType | null;
};

const TransportAndPayment: FC<TransportAndPaymentProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <TransportAndPaymentWrapper>
            <OrderSummaryContent>
                {props.transport !== null && (
                    <SummaryRow>
                        <TransportAndPaymentTextAndImage>
                            {props.transport.name}
                            <TransportAndPaymentImageWrapper>
                                <ImgStyled src="https://master.ssfwcc.ci.shopsys.cloud/content/images/transport/default/57.jpg" />
                            </TransportAndPaymentImageWrapper>
                        </TransportAndPaymentTextAndImage>
                        <TransportAndPaymentPrice>
                            <strong>
                                {formatPrice(props.transport.price.priceWithVat, props.transport.price.currencyCode, t)}
                            </strong>
                        </TransportAndPaymentPrice>
                    </SummaryRow>
                )}
                {props.payment !== null && (
                    <SummaryRow>
                        <TransportAndPaymentTextAndImage>
                            {props.payment.name}
                            <TransportAndPaymentImageWrapper>
                                <ImgStyled src="https://master.ssfwcc.ci.shopsys.cloud/content/images/payment/default/53.jpg" />
                            </TransportAndPaymentImageWrapper>
                        </TransportAndPaymentTextAndImage>
                        <TransportAndPaymentPrice>
                            <strong>
                                {formatPrice(props.payment.price.priceWithVat, props.payment.price.currencyCode, t)}
                            </strong>
                        </TransportAndPaymentPrice>
                    </SummaryRow>
                )}
            </OrderSummaryContent>
        </TransportAndPaymentWrapper>
    );
};

export default TransportAndPayment;
