import { ExpectedDeliveryDateSummary } from 'components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateSummary';
import { OrderItemGiftCard } from 'components/Blocks/OrderItemGiftCard/OrderItemGiftCard';
import { OrderItemProductCard } from 'components/Blocks/OrderItemProductCard/OrderItemProductCard';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { CartPreview } from 'components/Pages/Cart/CartPreview';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import { groupCartItemsWithGifts } from 'utils/cart/groupCartItemsWithGifts';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapCartItemAdditionalServiceSummaryLines } from 'utils/mappers/additionalServices';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderSummaryProps = {
    activeStep: number;
    isTransportOrPaymentLoading?: boolean;
};

export const OrderSummary: FC<OrderSummaryProps> = ({ activeStep, isTransportOrPaymentLoading }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();

    if (isCartFetchingOrUnavailable) {
        return <CartLoading />;
    }

    if (!cart) {
        return null;
    }

    const rows = groupCartItemsWithGifts(cart.items, []);
    const renderGift = (item: (typeof cart.items)[number], relatedProductNames?: string[], isNested = false) => (
        <OrderItemGiftCard
            key={item.uuid}
            availability={item.product.availability}
            categoryName={item.product.mainCategory?.name}
            fullName={item.product.fullName}
            isNested={isNested}
            mainImage={item.product.mainImage}
            quantity={item.quantity}
            relatedProductNames={relatedProductNames}
            totalPrice={
                isPriceVisible(item.product.giftPrice.priceWithVat)
                    ? String(mapPriceForCalculations(item.product.giftPrice.priceWithVat) * item.quantity)
                    : '***'
            }
            unit={item.product.unit.name}
            unitPrice={item.product.giftPrice.priceWithVat}
        />
    );

    return (
        <div className="vl:sticky vl:top-2 vl:col-span-1 flex flex-col gap-2 vl:self-start">
            <span className="h4">{t('Your order')}</span>

            {activeStep >= 3 && (
                <ExpectedDeliveryDateSummary expectedDeliveryDate={cart.transport?.expectedDeliveryDate} />
            )}

            <div className="relative">
                <ul
                    className={twJoin(
                        'flex max-h-125 flex-col gap-2 overflow-y-auto',
                        cart.items.length > 3 && 'pb-10',
                    )}
                >
                    {rows.map(({ cartItem: item, gifts }) => {
                        if (item.type === TypeCartItemTypeEnum.ProductGift) {
                            return renderGift(item);
                        }

                        const giftsContent =
                            gifts.length > 0
                                ? gifts.map(
                                      (gift) =>
                                          gift.state === 'added' &&
                                          renderGift(
                                              gift.cartItem,
                                              gift.relatedProducts.map((product) => product.fullName),
                                              true,
                                          ),
                                  )
                                : undefined;

                        return (
                            <OrderItemProductCard
                                key={item.uuid}
                                additionalServices={mapCartItemAdditionalServiceSummaryLines(
                                    item.additionalServices,
                                    item.quantity,
                                    item.product.unit.name,
                                    formatPrice,
                                )}
                                categoryName={item.product.mainCategory?.name ?? ''}
                                gifts={giftsContent}
                                freeQuantity={item.freeQuantity}
                                fullName={item.product.fullName}
                                mainImage={item.product.mainImage}
                                price={item.product.price}
                                product={item.product}
                                quantity={item.quantity}
                                unit={item.product.unit.name}
                            />
                        );
                    })}
                </ul>

                {cart.items.length > 3 && (
                    <div className="pointer-events-none absolute right-0 bottom-0 left-0 h-20 bg-linear-to-t from-white to-transparent" />
                )}
            </div>

            <CartPreview isTransportOrPaymentLoading={isTransportOrPaymentLoading} />
        </div>
    );
};
