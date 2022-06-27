import {
    ListItemDetailStyled,
    ListItemImageWrapperStyled,
    ListItemPriceStyled,
    ListItemQuantityStyled,
    ListItemStyled,
    ListItemTitleStyled,
} from './ListItem.style';
import Image from 'components/Basic/Image';
import RemoveCartItemButton from 'components/Pages/Cart/RemoveCartItemButton';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import NextLink from 'next/link';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type ListItemProps = {
    cartItem: CartItemType;
    listIndex: number;
};

const ListItem: FC<ListItemProps> = (props) => {
    const testIdentifier = 'layout-header-cart-listitem';

    const formatPrice = useFormatPrice();

    return (
        <ListItemStyled key={props.cartItem.uuid} data-testid={testIdentifier}>
            <ListItemImageWrapperStyled>
                <Image alt={props.cartItem.product.fullName} type="thumbnail" image={props.cartItem.product.image} />
            </ListItemImageWrapperStyled>
            <ListItemDetailStyled>
                <NextLink href={props.cartItem.product.slug} passHref>
                    <ListItemTitleStyled>{props.cartItem.product.fullName}</ListItemTitleStyled>
                </NextLink>
                <ListItemQuantityStyled>
                    {props.cartItem.quantity + props.cartItem.product.unit.name}
                </ListItemQuantityStyled>
                <ListItemPriceStyled>
                    {formatPrice(props.cartItem.product.price.priceWithVat * props.cartItem.quantity)}
                </ListItemPriceStyled>
            </ListItemDetailStyled>
            <RemoveCartItemButton cartItem={props.cartItem} listIndex={props.listIndex} />
        </ListItemStyled>
    );
};

export default ListItem;
