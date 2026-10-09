import { ExpectedDeliveryDateSummary } from 'components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateSummary';
import { OrderItemDiscountCard } from 'components/Blocks/OrderItemDiscountCard/OrderItemDiscountCard';
import { OrderItemGiftCard } from 'components/Blocks/OrderItemGiftCard/OrderItemGiftCard';
import { OrderItemProductCard } from 'components/Blocks/OrderItemProductCard/OrderItemProductCard';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapOrderItemAdditionalServiceSummaryLines } from 'utils/mappers/additionalServices';

type OrderConfirmationProductsProps = {
    expectedDeliveryDate: string | null;
    items: TypeOrderDetailItemFragment[] | undefined;
};

export const OrderConfirmationProducts: FC<OrderConfirmationProductsProps> = ({ expectedDeliveryDate, items }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    if (!items) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2">
            <span className="h4">{t('Your order')}</span>

            <ExpectedDeliveryDateSummary expectedDeliveryDate={expectedDeliveryDate} />

            <div className="relative">
                <ul className={twJoin('flex max-h-125 flex-col gap-2 overflow-y-auto', items.length > 3 && 'pb-10')}>
                    {items.map((item) => {
                        // prices of the order, not the current prices of the product, which can also be no longer available
                        const orderedUnitPrice = {
                            ...item.unitPrice,
                            __typename: 'ProductPrice' as const,
                            isPriceFrom: false,
                            nextPriceChange: null,
                            percentageDiscount: null,
                            basicPrice: item.unitPrice,
                        };

                        if (item.type === TypeOrderItemTypeEnum.Product) {
                            return (
                                <OrderItemProductCard
                                    key={item.uuid}
                                    additionalServices={mapOrderItemAdditionalServiceSummaryLines(
                                        item.relatedItems,
                                        formatPrice,
                                    )}
                                    areAdditionalServicePricesHighlighted={false}
                                    categoryName={item.product?.mainCategory?.name}
                                    freeQuantity={null}
                                    fullName={item.name}
                                    mainImage={item.product?.mainImage}
                                    price={orderedUnitPrice}
                                    quantity={item.quantity}
                                    unit={item.unit}
                                />
                            );
                        }

                        if (item.type === TypeOrderItemTypeEnum.ProductGift) {
                            return (
                                <OrderItemGiftCard
                                    key={item.uuid}
                                    categoryName={item.product?.mainCategory?.name}
                                    fullName={item.name}
                                    mainImage={item.product?.mainImage}
                                    price={orderedUnitPrice}
                                    quantity={item.quantity}
                                    unit={item.unit}
                                />
                            );
                        }

                        if (
                            item.type === TypeOrderItemTypeEnum.Discount ||
                            item.type === TypeOrderItemTypeEnum.Promotion
                        ) {
                            return (
                                <OrderItemDiscountCard
                                    key={item.uuid}
                                    name={item.name}
                                    price={item.totalPrice.priceWithVat}
                                />
                            );
                        }

                        return null;
                    })}
                </ul>

                {items.length > 3 && (
                    <div className="pointer-events-none absolute right-0 bottom-0 left-0 h-20 bg-linear-to-t from-white to-transparent" />
                )}
            </div>
        </div>
    );
};
