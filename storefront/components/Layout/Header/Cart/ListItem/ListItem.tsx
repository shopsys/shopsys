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
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { CartItemType } from 'types/cart';
import { formatPrice } from 'utils/formatting';

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
                <Image alt={props.cartItem.product.fullName} type="thumbnail" image={props.cartItem.product.image} />
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
