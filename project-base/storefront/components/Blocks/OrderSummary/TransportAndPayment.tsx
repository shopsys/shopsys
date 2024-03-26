import {
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { Image } from 'components/Basic/Image/Image';
import { SimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { PriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type TransportAndPaymentProps = {
    transport: TransportWithAvailablePaymentsAndStoresFragment | null;
    payment: SimplePaymentFragment | null;
    roundingPrice: PriceFragment | null;
};

export const TransportAndPayment: FC<TransportAndPaymentProps> = ({ payment, transport, roundingPrice }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper>
            <div className="table w-full">
                {transport && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage>
                            {transport.name}
                            <div className="flex h-8 w-8 items-center">
                                <Image alt={transport.name} height={32} src={transport.mainImage?.url} width={32} />
                            </div>
                        </OrderSummaryTextAndImage>
                        <OrderSummaryPrice>
                            <strong>{formatPrice(transport.price.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
                {payment && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage>
                            {payment.name}
                            <div className="flex h-8 w-8 items-center">
                                <Image alt={payment.name} height={32} src={payment.mainImage?.url} width={32} />
                            </div>
                        </OrderSummaryTextAndImage>
                        <OrderSummaryPrice>
                            <strong>{formatPrice(payment.price.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
                {roundingPrice && (
                    <OrderSummaryRow>
                        <OrderSummaryTextAndImage>{t('Rounding')}</OrderSummaryTextAndImage>
                        <OrderSummaryPrice>
                            <strong>{formatPrice(roundingPrice.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    </OrderSummaryRow>
                )}
            </div>
        </OrderSummaryRowWrapper>
    );
};
