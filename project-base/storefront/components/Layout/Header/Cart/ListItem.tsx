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

    return (
        <li
            className="flex w-full items-center border-b border-greyLighter py-3"
            key={uuid}
            data-testid={TEST_IDENTIFIER}
        >
            <div className="relative w-11 items-center">
                <Image image={product.image} alt={product.image?.name || product.fullName} type="thumbnail" />
            </div>
            <div className="flex flex-1 items-center justify-between">
                <ExtendedNextLink href={product.slug} passHref type="product">
                    <a className="flex-1 cursor-pointer pl-3 text-sm font-bold text-greyDark no-underline outline-none">
                        {product.fullName}
                    </a>
                </ExtendedNextLink>
                <span className="pr-3 text-sm">{quantity + product.unit.name}</span>
                <span className="w-28 break-words pr-4 text-right text-sm font-bold text-primary">
                    {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
                </span>
            </div>
            <RemoveCartItemButton onItemRemove={onItemRemove} />
        </li>
    );
};
