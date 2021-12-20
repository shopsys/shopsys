import {
    ListItemDetailStyled,
    ListItemImageWrapperStyled,
    ListItemPriceStyled,
    ListItemQuantityStyled,
    ListItemStyled,
    ListItemTitleStyled,
} from './ListItem.style';
import { CartItemType } from 'types/cart';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image';
import NextLink from 'next/link';
import RemoveCartItemButton from 'components/Pages/Cart/RemoveCartItemButton';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ListItemProps = {
    cartItem: CartItemType;
};

const ListItem: FC<ListItemProps> = (props) => {
    const testIdentifier = 'layout-header-cart-listitem';

    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);

    return (
        <ListItemStyled key={props.cartItem.uuid} data-testid={testIdentifier}>
            <ListItemImageWrapperStyled>
                <Image alt={props.cartItem.product.fullName} image={props.cartItem.product.image} />
            </ListItemImageWrapperStyled>
            <ListItemDetailStyled>
                <NextLink href={props.cartItem.product.slug}>
                    <ListItemTitleStyled>{props.cartItem.product.fullName}</ListItemTitleStyled>
                </NextLink>
                <ListItemQuantityStyled>
                    {props.cartItem.quantity + props.cartItem.product.unit.name}
                </ListItemQuantityStyled>
                <ListItemPriceStyled>
                    {formatPrice(
                        props.cartItem.product.price.priceWithVat * props.cartItem.quantity,
                        domainConfig.currencyCode,
                        t,
                    )}
                </ListItemPriceStyled>
            </ListItemDetailStyled>
            <RemoveCartItemButton cartItemUuid={props.cartItem.uuid} />
        </ListItemStyled>
    );
};

export default ListItem;
