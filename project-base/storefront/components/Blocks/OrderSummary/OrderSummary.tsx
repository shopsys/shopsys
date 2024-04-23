import { PromoCode } from './PromoCode';
import { SingleProduct } from './SingleProduct';
import { TotalPrice } from './TotalPrice';
import { TransportAndPayment } from './TransportAndPayment';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCart } from 'utils/cart/useCurrentCart';

type OrderSummaryProps = {
    isTransportOrPaymentLoading?: boolean;
};

export const OrderSummary: FC<OrderSummaryProps> = ({ isTransportOrPaymentLoading }) => {
    const { t } = useTranslation();
    const { cart, transport, payment, promoCode, roundingPrice, isFetching } = useCurrentCart();

    if (isFetching) {
        return <CartLoading />;
    }

    if (!cart) {
        return null;
    }

    return (
        <>
            <Adverts withGapBottom positionName="cartPreview" />
            <div className="w-full vl:max-w-md">
                <h3 className="mb-3 font-bold lg:text-lg">{t('Your order')}</h3>

                <div className="rounded bg-whiteSnow py-3 px-5 vl:m-0">
                    <div className="relative flex flex-col">
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
                            {(transport || payment) && (
                                <TransportAndPayment
                                    payment={payment}
                                    roundingPrice={roundingPrice}
                                    transport={transport}
                                />
                            )}
                            {promoCode && <PromoCode discount={cart.totalDiscountPrice} promoCode={promoCode} />}
                        </div>

                        <TotalPrice totalPrice={cart.totalPrice} />
                    </div>
                </div>
            </div>
        </>
    );
};
