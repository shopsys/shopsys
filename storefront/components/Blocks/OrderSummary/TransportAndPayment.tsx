import {
    OrderSummaryContent,
    SummaryRow,
    TransportAndPaymentImageWrapper,
    TransportAndPaymentPrice,
    TransportAndPaymentTextAndImage,
    TransportAndPaymentWrapper,
} from './OrderSummary.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image';
import { PaymentType } from 'types/payment';
import { TransportType } from 'types/transport';
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
                                <Image image={props.transport.image} alt={props.transport.name} />
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
                                <Image image={props.payment.image} alt={props.payment.name} />
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
