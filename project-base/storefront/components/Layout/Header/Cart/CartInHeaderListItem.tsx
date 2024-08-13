import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { MouseEventHandler } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type CartInHeaderListItemProps = {
    cartItem: TypeCartItemFragment;
    onRemoveFromCart: MouseEventHandler<HTMLButtonElement>;
};

export const CartInHeaderListItem: FC<CartInHeaderListItemProps> = ({
    cartItem: { product, uuid, quantity },
    onRemoveFromCart,
}) => {
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    return (
        <li key={uuid} className="flex w-full items-center gap-x-3 border-b border-borderAccent py-3">
            <div className="flex w-11 items-center justify-center" tid={TIDs.header_cart_list_item_image}>
                <Image
                    alt={product.mainImage?.name || product.fullName}
                    className="max-h-11 w-auto"
                    height={44}
                    src={product.mainImage?.url}
                    width={44}
                />
            </div>

            <ExtendedNextLink
                className="flex-1 cursor-pointer text-sm font-bold outline-none"
                href={productSlug}
                type="product"
            >
                {product.fullName}
            </ExtendedNextLink>

            <div className="text-sm">{quantity + product.unit.name}</div>

            {isPriceVisible(product.price.priceWithVat) && (
                <div className="w-28 break-words text-right text-sm font-bold text-price">
                    {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                </div>
            )}

            <RemoveCartItemButton onRemoveFromCart={onRemoveFromCart} />
        </li>
    );
};
