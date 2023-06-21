import {
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { Image } from 'components/Basic/Image/Image';
import { SimplePaymentFragmentApi, TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';

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
                                    image={getFirstImageOrNull(transport.images)}
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
                                <Image
                                    image={getFirstImageOrNull(payment.images)}
                                    type="default"
                                    alt={payment.name}
                                    className="w-9"
                                />
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
