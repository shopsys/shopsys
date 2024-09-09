import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { MouseEventHandler } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';
import { isPriceVisible } from 'utils/mappers/price';

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
    const isProductPriceVisible = isPriceVisible(product.price.priceWithVat);

    return (
        <li
            key={uuid}
            className="relative flex h-auto w-full flex-row flex-wrap items-center gap-x-6 border-b border-borderAccentLess py-3 last:border-b-[3px] lg:flex-nowrap"
        >
            <div className="flex min-h-20 w-full flex-row items-center gap-x-6">
                <ExtendedNextLink
                    className="flex w-20 items-center justify-center"
                    href={productSlug}
                    tid={TIDs.header_cart_list_item_image}
                    type="product"
                >
                    <Image
                        alt={product.mainImage?.name || product.fullName}
                        className="max-h-20 w-auto"
                        height={80}
                        src={product.mainImage?.url}
                        width={80}
                    />
                </ExtendedNextLink>

                <ExtendedNextLink
                    className="flex-1 cursor-pointer font-secondary text-sm font-semibold text-tableText no-underline outline-none hover:text-link hover:underline"
                    href={productSlug}
                    type="product"
                >
                    {product.fullName}
                </ExtendedNextLink>
            </div>
            <div className="mt-2 flex flex-row gap-x-6 lg:mt-0 lg:w-auto">
                <div className="w-20 text-center font-secondary text-sm font-semibold">
                    {quantity + ' ' + product.unit.name}
                </div>

                {isProductPriceVisible && (
                    <div className="w-28 break-words font-secondary font-bold text-price lg:text-right">
                        {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                    </div>
                )}
            </div>
            <RemoveCartItemButton
                className="absolute right-0 top-2 lg:relative lg:right-0 lg:top-0"
                onRemoveFromCart={onRemoveFromCart}
            />
        </li>
    );
};
