import {
    OrderSummaryPriceStyled,
    OrderSummaryRowContentStyled,
    OrderSummaryRowStyled,
    OrderSummaryRowWrapperStyled,
    OrderSummaryTextAndImageStyled,
    TransportAndPaymentImageWrapperStyled,
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
        <OrderSummaryRowWrapperStyled data-testid={testIdentifier}>
            <OrderSummaryRowContentStyled>
                {props.transport !== null && (
                    <OrderSummaryRowStyled>
                        <OrderSummaryTextAndImageStyled data-testid={testIdentifier + '-transport-name'}>
                            {props.transport.name}
                            <TransportAndPaymentImageWrapperStyled>
                                <Image image={props.transport.image} type="default" alt={props.transport.name} />
                            </TransportAndPaymentImageWrapperStyled>
                        </OrderSummaryTextAndImageStyled>
                        <OrderSummaryPriceStyled data-testid={testIdentifier + '-transport-price'}>
                            <strong>
                                {formatPrice(props.transport.price.priceWithVat, props.transport.price.currencyCode, t)}
                            </strong>
                        </OrderSummaryPriceStyled>
                    </OrderSummaryRowStyled>
                )}
                {props.payment !== null && (
                    <OrderSummaryRowStyled>
                        <OrderSummaryTextAndImageStyled data-testid={testIdentifier + '-payment-name'}>
                            {props.payment.name}
                            <TransportAndPaymentImageWrapperStyled>
                                <Image image={props.payment.image} type="default" alt={props.payment.name} />
                            </TransportAndPaymentImageWrapperStyled>
                        </OrderSummaryTextAndImageStyled>
                        <OrderSummaryPriceStyled data-testid={testIdentifier + '-payment-price'}>
                            <strong>
                                {formatPrice(props.payment.price.priceWithVat, props.payment.price.currencyCode, t)}
                            </strong>
                        </OrderSummaryPriceStyled>
                    </OrderSummaryRowStyled>
                )}
            </OrderSummaryRowContentStyled>
        </OrderSummaryRowWrapperStyled>
    );
};

export default TransportAndPayment;
