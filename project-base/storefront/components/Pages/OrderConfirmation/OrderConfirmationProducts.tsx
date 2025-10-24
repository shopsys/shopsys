import { OrderItemDiscountCard } from 'components/Blocks/OrderItemDiscountCard/OrderItemDiscountCard';
import { OrderItemGiftCard } from 'components/Blocks/OrderItemGiftCard/OrderItemGiftCard';
import { OrderItemProductCard } from 'components/Blocks/OrderItemProductCard/OrderItemProductCard';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import React from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type OrderConfirmationProductsProps = {
    items: TypeOrderDetailItemFragment[] | undefined;
};

export const OrderConfirmationProducts: FC<OrderConfirmationProductsProps> = ({ items }) => {
    const { t } = useTranslation();

    if (!items) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2">
            <span className="h4">{t('Your order')}</span>

            <div className="relative">
                <ul
                    className={twJoin('flex max-h-[500px] flex-col gap-2 overflow-y-auto', items.length > 3 && 'pb-10')}
                >
                    {items.map((item) => {
                        if (item.type === TypeOrderItemTypeEnum.Product && item.product) {
                            return (
                                <OrderItemProductCard
                                    key={item.uuid}
                                    availability={item.product.availability}
                                    categoryName={item.product.categories[0]?.name}
                                    freeQuantity={null}
                                    fullName={item.name}
                                    mainImage={item.product.mainImage}
                                    price={item.product.price}
                                    quantity={item.quantity}
                                    unit={item.unit}
                                />
                            );
                        }

                        if (item.type === TypeOrderItemTypeEnum.ProductGift && item.product) {
                            return (
                                <OrderItemGiftCard
                                    key={item.uuid}
                                    availability={item.product.availability}
                                    categoryName={item.product.categories[0]?.name}
                                    fullName={item.name}
                                    mainImage={item.product.mainImage}
                                    price={item.product.giftPrice}
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
                    <div className="pointer-events-none absolute right-0 bottom-0 left-0 h-20 bg-gradient-to-t from-white to-transparent" />
                )}
            </div>
        </div>
    );
};
