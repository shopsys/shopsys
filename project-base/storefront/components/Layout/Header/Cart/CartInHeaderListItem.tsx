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
        <li
            key={uuid}
            className="flex flex-row flex-wrap w-full items-center gap-x-6 border-b border-borderAccentLess py-3 last:border-b-[3px] h-auto lg:flex-nowrap relative"
        >
            <div className="flex flex-row gap-x-6 items-center w-full min-h-20">
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
                    className="flex-1 cursor-pointer text-sm font-semibold outline-none no-underline text-tableText hover:text-link hover:underline"
                    href={productSlug}
                    type="product"
                >
                    {product.fullName}
                </ExtendedNextLink>
            </div>
            <div className="flex flex-row lg:w-full lg:w-auto gap-x-6 mt-2 lg:mt-0">
                <div className="text-sm w-20 text-center">{quantity + ' ' + product.unit.name}</div>

                <div className="w-28 break-words lg:text-right font-bold text-price">
                    {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                </div>
            </div>
            <RemoveCartItemButton
                className="absolute right-0 top-2 lg:relative lg:right-0 lg:top-0"
                onRemoveFromCart={onRemoveFromCart}
            />
        </li>
    );
};
