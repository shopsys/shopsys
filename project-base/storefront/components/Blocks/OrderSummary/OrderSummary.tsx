import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { OrderItemProductCard } from 'components/Blocks/OrderItemProductCard/OrderItemProductCard';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { OrderConfirmationSummary } from 'components/Pages/OrderConfirmation/OrderConfirmationSummary';
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

    return (
        <div className="vl:col-span-1 flex flex-col gap-2">
            <span className="h4">{t('Your order')}</span>

            {cart.items.map((item) => (
                <OrderItemProductCard
                    key={item.uuid}
                    availability={item.product.availability}
                    categoryName={item.product.categories[0]?.name}
                    fullName={item.product.fullName}
                    mainImage={item.product.mainImage}
                    price={item.product.price}
                    quantity={item.quantity}
                    unit={item.product.unit.name}
                />
            ))}

            <div className="relative">
                {isTransportOrPaymentLoading && (transport || payment) && (
                    <LoaderWithOverlay className="w-8" overlayClassName="rounded-xl" />
                )}

                <OrderConfirmationSummary
                    promoCode={promoCodes[0]?.code}
                    roundingPrice={roundingPrice}
                    totalPrice={cart.totalPrice}
                    payment={{
                        name: payment?.name,
                        price: payment?.price.priceWithVat,
                    }}
                    transport={{
                        name: transport?.name,
                        price: transport?.price.priceWithVat,
                    }}
                />
            </div>
        </div>
    );
};
