import { OrderItemDiscountCard } from 'components/Blocks/OrderItemDiscountCard/OrderItemDiscountCard';
import { OrderItemProductCard } from 'components/Blocks/OrderItemProductCard/OrderItemProductCard';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import React from 'react';

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

            {items.map((item) => {
                if (item.type === TypeOrderItemTypeEnum.Product && item.product) {
                    return (
                        <OrderItemProductCard
                            key={item.uuid}
                            availability={item.product.availability}
                            categoryName={item.product.categories[0]?.name}
                            fullName={item.name}
                            mainImage={item.product.mainImage}
                            price={item.product.price}
                            quantity={item.quantity}
                            unit={item.unit}
                        />
                    );
                }

                if (item.type === TypeOrderItemTypeEnum.Discount) {
                    return (
                        <OrderItemDiscountCard key={item.uuid} name={item.name} price={item.totalPrice.priceWithVat} />
                    );
                }

                return null;
            })}
        </div>
    );
};
