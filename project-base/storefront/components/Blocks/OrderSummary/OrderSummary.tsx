import { PromoCode } from './PromoCode';
import { SingleProduct } from './SingleProduct';
import { TotalPrice } from './TotalPrice';
import { TransportAndPayment } from './TransportAndPayment';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { useCurrentCart } from 'connectors/cart/Cart';
import useTranslation from 'next-translate/useTranslation';

type OrderSummaryProps = {
    isTransportOrPaymentLoading?: boolean;
};

const TEST_IDENTIFIER = 'blocks-ordersummary';

export const OrderSummary: FC<OrderSummaryProps> = ({ isTransportOrPaymentLoading }) => {
    const { t } = useTranslation();
    const { cart, transport, payment, promoCode, roundingPrice } = useCurrentCart();

    if (cart === null) {
        return null;
    }

    return (
        <>
            <Adverts positionName="cartPreview" withGapBottom />
            <div className="w-full vl:max-w-md" data-testid={TEST_IDENTIFIER}>
                <h3 className="mb-3 font-bold lg:text-lg">{t('Your order')}</h3>

                <div className="rounded bg-greyVeryLight py-3 px-5 vl:m-0">
                    <div className="relative flex flex-col">
                        <div className="mb-5" data-testid={TEST_IDENTIFIER}>
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
                                    transport={transport}
                                    payment={payment}
                                    roundingPrice={roundingPrice}
                                />
                            )}
                            {promoCode && <PromoCode promoCode={promoCode} discount={cart.totalDiscountPrice} />}
                        </div>

                        <TotalPrice totalPrice={cart.totalPrice} />
                    </div>
                </div>
            </div>
        </>
    );
};
