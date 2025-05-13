import { PromoCode } from './PromoCode';
import { SingleProduct } from './SingleProduct';
import { TotalPrice } from './TotalPrice';
import { TransportAndPayment } from './TransportAndPayment';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { AnimatePresence } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCart } from 'utils/cart/useCurrentCart';

type OrderSummaryProps = {
    isTransportOrPaymentLoading?: boolean;
};

export const OrderSummary: FC<OrderSummaryProps> = ({ isTransportOrPaymentLoading }) => {
    const { t } = useTranslation();
    const { cart, transport, payment, promoCodes, roundingPrice, isCartFetchingOrUnavailable } = useCurrentCart();

    if (isCartFetchingOrUnavailable) {
        return <CartLoading />;
    }

    if (!cart) {
        return null;
    }

    const totalDiscount = cart.totalDiscountPrice;

    return (
        <>
            <Adverts className="mb-4" positionName="cartPreview" />

            <div className="vl:max-w-md w-full">
                <h4 className="mb-3">{t('Your order')}</h4>

                <div className="bg-backgroundMore vl:m-0 rounded-sm">
                    <div className="relative flex flex-col px-5 py-3">
                        <div className="mb-5">
                            <ul>
                                {cart.items.map((item) => (
                                    <SingleProduct key={item.uuid} item={item} />
                                ))}
                            </ul>
                        </div>

                        <div>
                            {isTransportOrPaymentLoading && (transport || payment) && (
                                <LoaderWithOverlay className="w-8" />
                            )}
                            <AnimatePresence initial={false}>
                                {(transport || payment) && (
                                    <AnimateCollapseDiv className="!flex w-full" keyName="transport-order-summary">
                                        <TransportAndPayment
                                            payment={payment}
                                            roundingPrice={roundingPrice}
                                            transport={transport}
                                        />
                                    </AnimateCollapseDiv>
                                )}
                            </AnimatePresence>

                            {promoCodes.length > 0 && (
                                <PromoCode
                                    key={promoCodes[0].code}
                                    code={promoCodes[0].code}
                                    discount={totalDiscount}
                                />
                            )}
                        </div>

                        <TotalPrice totalPrice={cart.totalPrice} />
                    </div>
                </div>
            </div>
        </>
    );
};
