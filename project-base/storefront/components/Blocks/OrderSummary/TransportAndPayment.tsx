import {
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { Image } from 'components/Basic/Image/Image';
import {
    PriceFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type TransportAndPaymentProps = {
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null;
    payment: SimplePaymentFragmentApi | null;
    roundingPrice: PriceFragmentApi | null;
};

const TEST_IDENTIFIER = 'blocks-ordersummary';

export const TransportAndPayment: FC<TransportAndPaymentProps> = ({ payment, transport, roundingPrice }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper dataTestId={TEST_IDENTIFIER}>
            <div className="table w-full">
                {transport && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage dataTestId={TEST_IDENTIFIER + '-transport-name'}>
                            {transport.name}
                            <div className="flex h-8 w-8 items-center">
                                <Image alt={transport.name} height={32} src={transport.mainImage?.url} width={32} />
                            </div>
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
                            <div className="flex h-8 w-8 items-center">
                                <Image alt={payment.name} height={32} src={payment.mainImage?.url} width={32} />
                            </div>
                        </OrderSummaryTextAndImage>
                        <OrderSummaryPrice dataTestId={TEST_IDENTIFIER + '-payment-price'}>
                            <strong>{formatPrice(payment.price.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
                {roundingPrice && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage dataTestId={TEST_IDENTIFIER + '-rounding-name'}>
                            {t('Rounding')}
                        </OrderSummaryTextAndImage>
                        <OrderSummaryPrice dataTestId={TEST_IDENTIFIER + '-rounding-price'}>
                            <strong>{formatPrice(roundingPrice.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
            </div>
        </OrderSummaryRowWrapper>
    );
};
