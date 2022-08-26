import {
    OrderSummaryPriceStyled,
    OrderSummaryRowContentStyled,
    OrderSummaryRowStyled,
    OrderSummaryRowWrapperStyled,
    OrderSummaryTextAndImageStyled,
    TransportAndPaymentImageWrapperStyled,
} from './OrderSummary.style';
import { Image } from 'components/Basic/Image/Image';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { FC } from 'react';
import { PaymentType } from 'types/payment';
import { TransportType } from 'types/transport';

type TransportAndPaymentProps = {
    transport: TransportType | null;
    payment: PaymentType | null;
};

const TEST_IDENTIFIER = 'blocks-ordersummary';

export const TransportAndPayment: FC<TransportAndPaymentProps> = ({ payment, transport }) => {
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapperStyled data-testid={TEST_IDENTIFIER}>
            <OrderSummaryRowContentStyled>
                {transport !== null && (
                    <OrderSummaryRowStyled>
                        <OrderSummaryTextAndImageStyled data-testid={TEST_IDENTIFIER + '-transport-name'}>
                            {transport.name}
                            <TransportAndPaymentImageWrapperStyled>
                                <Image image={transport.image} type="default" alt={transport.name} />
                            </TransportAndPaymentImageWrapperStyled>
                        </OrderSummaryTextAndImageStyled>
                        <OrderSummaryPriceStyled data-testid={TEST_IDENTIFIER + '-transport-price'}>
                            <strong>{formatPrice(transport.price.priceWithVat)}</strong>
                        </OrderSummaryPriceStyled>
                    </OrderSummaryRowStyled>
                )}
                {payment !== null && (
                    <OrderSummaryRowStyled>
                        <OrderSummaryTextAndImageStyled data-testid={TEST_IDENTIFIER + '-payment-name'}>
                            {payment.name}
                            <TransportAndPaymentImageWrapperStyled>
                                <Image image={payment.image} type="default" alt={payment.name} />
                            </TransportAndPaymentImageWrapperStyled>
                        </OrderSummaryTextAndImageStyled>
                        <OrderSummaryPriceStyled data-testid={TEST_IDENTIFIER + '-payment-price'}>
                            <strong>{formatPrice(payment.price.priceWithVat)}</strong>
                        </OrderSummaryPriceStyled>
                    </OrderSummaryRowStyled>
                )}
            </OrderSummaryRowContentStyled>
        </OrderSummaryRowWrapperStyled>
    );
};
