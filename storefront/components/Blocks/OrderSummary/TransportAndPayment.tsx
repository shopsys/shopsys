import {
    OrderSummaryContent,
    SummaryPrice,
    SummaryRow,
    SummaryTextAndImage,
    SummaryWrapper,
    TransportAndPaymentImageWrapper,
} from './OrderSummary.style';
import Image from 'components/Basic/Image';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { PaymentType } from 'types/payment';
import { TransportType } from 'types/transport';
import { formatPrice } from 'utils/formatting';

type TransportAndPaymentProps = {
    transport: TransportType | null;
    payment: PaymentType | null;
};

const TransportAndPayment: FC<TransportAndPaymentProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary';

    const t = useTypedTranslationFunction();

    return (
        <SummaryWrapper data-testid={testIdentifier}>
            <OrderSummaryContent>
                {props.transport !== null && (
                    <SummaryRow>
                        <SummaryTextAndImage data-testid={testIdentifier + '-transport-name'}>
                            {props.transport.name}
                            <TransportAndPaymentImageWrapper>
                                <Image image={props.transport.image} type="default" alt={props.transport.name} />
                            </TransportAndPaymentImageWrapper>
                        </SummaryTextAndImage>
                        <SummaryPrice data-testid={testIdentifier + '-transport-price'}>
                            <strong>
                                {formatPrice(props.transport.price.priceWithVat, props.transport.price.currencyCode, t)}
                            </strong>
                        </SummaryPrice>
                    </SummaryRow>
                )}
                {props.payment !== null && (
                    <SummaryRow>
                        <SummaryTextAndImage data-testid={testIdentifier + '-payment-name'}>
                            {props.payment.name}
                            <TransportAndPaymentImageWrapper>
                                <Image image={props.payment.image} type="default" alt={props.payment.name} />
                            </TransportAndPaymentImageWrapper>
                        </SummaryTextAndImage>
                        <SummaryPrice data-testid={testIdentifier + '-payment-price'}>
                            <strong>
                                {formatPrice(props.payment.price.priceWithVat, props.payment.price.currencyCode, t)}
                            </strong>
                        </SummaryPrice>
                    </SummaryRow>
                )}
            </OrderSummaryContent>
        </SummaryWrapper>
    );
};

export default TransportAndPayment;
