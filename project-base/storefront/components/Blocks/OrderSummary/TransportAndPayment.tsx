import {
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type TransportAndPaymentProps = {
    transport: TypeTransportWithAvailablePaymentsFragment | null;
    payment: TypeSimplePaymentFragment | null;
    roundingPrice: TypePriceFragment | null;
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
                            <div
                                className={twJoin('flex h-8 w-8 items-center', !transport.mainImage?.url && 'hidden')}
                                tid={TIDs.order_summary_transport_and_payment_image}
                            >
                                <Image
                                    alt={transport.name}
                                    className="max-h-8 w-auto"
                                    height={32}
                                    src={transport.mainImage?.url}
                                    width={32}
                                />
                            </div>
                        </OrderSummaryTextAndImage>
                        {isPriceVisible(transport.price.priceWithVat) && (
                            <OrderSummaryPrice>
                                <strong>{formatPrice(transport.price.priceWithVat)}</strong>
                            </OrderSummaryPrice>
                        )}
                    </OrderSummaryRow>
                )}
                <AnimatePresence initial={false}>
                    {payment && (
                        <AnimateCollapseDiv className="!flex w-full" keyName="payment-order-summary">
                            <OrderSummaryRow>
                                <OrderSummaryTextAndImage>
                                    {payment.name}
                                    <div
                                        tid={TIDs.order_summary_transport_and_payment_image}
                                        className={twJoin(
                                            'flex h-8 w-8 items-center',
                                            !payment.mainImage?.url && 'hidden',
                                        )}
                                    >
                                        <Image
                                            alt={payment.name}
                                            className="max-h-8 w-auto"
                                            height={32}
                                            src={payment.mainImage?.url}
                                            width={32}
                                        />
                                    </div>
                                </OrderSummaryTextAndImage>
                                {isPriceVisible(payment.price.priceWithVat) && (
                                    <OrderSummaryPrice>
                                        <strong>{formatPrice(payment.price.priceWithVat)}</strong>
                                    </OrderSummaryPrice>
                                )}
                            </OrderSummaryRow>
                        </AnimateCollapseDiv>
                    )}
                </AnimatePresence>
                {roundingPrice && isPriceVisible(roundingPrice.priceWithVat) && (
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
