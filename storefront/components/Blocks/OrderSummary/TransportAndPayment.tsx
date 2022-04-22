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
    const testIdentifier = 'blocks-ordersummary';

    const t = useTypedTranslationFunction();

    return (
        <TransportAndPaymentWrapper data-testid={testIdentifier}>
            <OrderSummaryContent>
                {props.transport !== null && (
                    <SummaryRow>
                        <TransportAndPaymentTextAndImage data-testid={testIdentifier + '-transport-name'}>
                            {props.transport.name}
                            <TransportAndPaymentImageWrapper>
                                <Image image={props.transport.image} type="default" alt={props.transport.name} />
                            </TransportAndPaymentImageWrapper>
                        </TransportAndPaymentTextAndImage>
                        <TransportAndPaymentPrice data-testid={testIdentifier + '-transport-price'}>
                            <strong>
                                {formatPrice(props.transport.price.priceWithVat, props.transport.price.currencyCode, t)}
                            </strong>
                        </TransportAndPaymentPrice>
                    </SummaryRow>
                )}
                {props.payment !== null && (
                    <SummaryRow>
                        <TransportAndPaymentTextAndImage data-testid={testIdentifier + '-payment-name'}>
                            {props.payment.name}
                            <TransportAndPaymentImageWrapper>
                                <Image image={props.payment.image} type="default" alt={props.payment.name} />
                            </TransportAndPaymentImageWrapper>
                        </TransportAndPaymentTextAndImage>
                        <TransportAndPaymentPrice data-testid={testIdentifier + '-payment-price'}>
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
