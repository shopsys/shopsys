import {
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { Image } from 'components/Basic/Image/Image';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { SimplePaymentFragmentApi } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';

type TransportAndPaymentProps = {
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null;
    payment: SimplePaymentFragmentApi | null;
};

const TEST_IDENTIFIER = 'blocks-ordersummary';

export const TransportAndPayment: FC<TransportAndPaymentProps> = ({ payment, transport }) => {
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper dataTestId={TEST_IDENTIFIER}>
            <div className="table w-full">
                {transport && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage dataTestId={TEST_IDENTIFIER + '-transport-name'}>
                            {transport.name}
                            <span className="ml-2 inline-block h-5 align-bottom">
                                <Image
                                    image={transport.mainImage}
                                    type="default"
                                    alt={transport.name}
                                    className="w-9"
                                />
                            </span>
                        </OrderSummaryTextAndImage>
                        <OrderSummaryPrice dataTestId={TEST_IDENTIFIER + '-transport-price'}>
                            <strong>{formatPrice(transport.price.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
                {payment && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage dataTestId={TEST_IDENTIFIER + '-payment-name'}>
                            {payment.name}
                            <span className="ml-2 inline-block h-5 align-bottom">
                                <Image image={payment.mainImage} type="default" alt={payment.name} className="w-9" />
                            </span>
                        </OrderSummaryTextAndImage>
                        <OrderSummaryPrice dataTestId={TEST_IDENTIFIER + '-payment-price'}>
                            <strong>{formatPrice(payment.price.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
            </div>
        </OrderSummaryRowWrapper>
    );
};
