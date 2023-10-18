import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { CartItemFragmentApi } from 'graphql/generated';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { MouseEventHandler } from 'react';

type ListItemProps = {
    cartItem: CartItemFragmentApi;
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'layout-header-cart-listitem';

export const ListItem: FC<ListItemProps> = ({ cartItem: { product, uuid, quantity }, onItemRemove }) => {
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    return (
        <li
            key={uuid}
            className="flex w-full items-center gap-x-3 border-b border-greyLighter py-3"
            data-testid={TEST_IDENTIFIER}
        >
            <Image
                alt={product.mainImage?.name || product.fullName}
                className="h-11 w-11"
                image={product.mainImage}
                type="thumbnail"
            />

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
