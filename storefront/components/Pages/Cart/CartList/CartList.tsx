import { ListStyled } from './CartList.style';
import { CartListItem } from './CartListItem/CartListItem';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Webline } from 'components/Layout/Webline/Webline';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { RemoveFromCartHandler, useRemoveFromCart } from 'hooks/cart/useRemoveFromCart';
import { FC, useCallback } from 'react';
import { CartItemType } from 'types/cart';

type CartListProps = {
    items?: CartItemType[];
};

export const CartList: FC<CartListProps> = ({ items }) => {
    const [removeItemFromCart, isRemovingItem] = useRemoveFromCart();
    const [changeCartItemQuantity, isChangingCartsItem] = useAddToCart('cart');

    const removeItemHandler = useCallback<RemoveFromCartHandler>(
        (cartItem, listIndex, gtmListName) => removeItemFromCart(cartItem, listIndex, gtmListName),
        [removeItemFromCart],
    );

    if (items === undefined) {
        return null;
    }

    return (
        <Webline>
            <ListStyled>
                {(isRemovingItem || isChangingCartsItem) && <LoaderWithOverlay iconSize={64} />}
                {items.map((item, index) => (
                    <CartListItem
                        key={item.uuid}
                        item={item}
                        listIndex={index}
                        onItemRemove={() => removeItemHandler(item, index, 'cart')}
                        onItemQuantityChange={changeCartItemQuantity}
                    />
                ))}
            </ListStyled>
        </Webline>
    );
};
