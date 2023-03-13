import { CartListItem } from './CartListItem';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartItemFragmentApi } from 'graphql/generated';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { RemoveFromCartHandler, useRemoveFromCart } from 'hooks/cart/useRemoveFromCart';
import { useCallback } from 'react';

type CartListProps = {
    items?: CartItemFragmentApi[];
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
            <ul className="relative mb-6 border border-b-0 border-greyLighter lg:mb-8 lg:border-none">
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
            </ul>
        </Webline>
    );
};
