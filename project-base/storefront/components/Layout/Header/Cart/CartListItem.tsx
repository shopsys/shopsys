import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { MouseEventHandler } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';

type ListItemProps = {
    cartItem: TypeCartItemFragment;
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

export const ListItem: FC<ListItemProps> = ({ cartItem: { product, uuid, quantity }, onItemRemove }) => {
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    return (
        <li key={uuid} className="flex w-full items-center gap-x-3 border-b border-greyLighter py-3">
            <div className="flex w-11 items-center justify-center">
                <Image
                    alt={product.mainImage?.name || product.fullName}
                    className="max-h-11 w-auto"
                    height={44}
                    src={product.mainImage?.url}
                    width={44}
                />
            </div>

            <ExtendedNextLink
                className="flex-1 cursor-pointer text-sm font-bold text-greyDark no-underline outline-none"
                href={productSlug}
                type="product"
            >
                {product.fullName}
            </ExtendedNextLink>

            <div className="text-sm">{quantity + product.unit.name}</div>

            <div className="w-28 break-words text-right text-sm font-bold text-primary">
                {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
            </div>

            <RemoveCartItemButton onItemRemove={onItemRemove} />
        </li>
    );
};
