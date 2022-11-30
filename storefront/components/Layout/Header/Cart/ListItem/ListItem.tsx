import {
    ListItemDetailStyled,
    ListItemImageWrapperStyled,
    ListItemPriceStyled,
    ListItemQuantityStyled,
    ListItemStyled,
    ListItemTitleStyled,
} from './ListItem.style';
import { Image } from 'components/Basic/Image/Image';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton/RemoveCartItemButton';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import NextLink from 'next/link';
import { FC, MouseEventHandler } from 'react';
import { CartItemType } from 'types/cart';

type ListItemProps = {
    cartItem: CartItemType;
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
};

const TEST_IDENTIFIER = 'layout-header-cart-listitem';

export const ListItem: FC<ListItemProps> = ({ cartItem, onItemRemove }) => {
    const formatPrice = useFormatPrice();

    return (
        <ListItemStyled key={cartItem.uuid} data-testid={TEST_IDENTIFIER}>
            <ListItemImageWrapperStyled>
                <Image alt={cartItem.product.fullName} type="thumbnail" image={cartItem.product.image} />
            </ListItemImageWrapperStyled>
            <ListItemDetailStyled>
                <NextLink href={cartItem.product.slug} passHref>
                    <ListItemTitleStyled>{cartItem.product.fullName}</ListItemTitleStyled>
                </NextLink>
                <ListItemQuantityStyled>{cartItem.quantity + cartItem.product.unit.name}</ListItemQuantityStyled>
                <ListItemPriceStyled>
                    {formatPrice(cartItem.product.price.priceWithVat * cartItem.quantity)}
                </ListItemPriceStyled>
            </ListItemDetailStyled>
            <RemoveCartItemButton onItemRemove={onItemRemove} />
        </ListItemStyled>
    );
};
