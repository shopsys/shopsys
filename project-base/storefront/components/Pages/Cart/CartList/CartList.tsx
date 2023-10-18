import { CartListItem } from './CartListItem';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { CartItemFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { useRemoveFromCart } from 'hooks/cart/useRemoveFromCart';

type CartListProps = {
    items: CartItemFragmentApi[];
};

export const CartList: FC<CartListProps> = ({ items: cartItems }) => {
    const [removeItemFromCart, isRemovingItem] = useRemoveFromCart(GtmProductListNameType.cart);
    const [changeCartItemQuantity, isChangingCartsItem] = useAddToCart(
        GtmMessageOriginType.cart,
        GtmProductListNameType.cart,
    );

    return (
        <ul className="relative mb-6 border-greyLighter lg:mb-8">
            {(isRemovingItem || isChangingCartsItem) && <LoaderWithOverlay className="w-16" />}
            {cartItems.map((cartItem, listIndex) => (
                <CartListItem
                    key={cartItem.uuid}
                    item={cartItem}
                    listIndex={listIndex}
                    onItemQuantityChange={changeCartItemQuantity}
                    onItemRemove={() => removeItemFromCart(cartItem, listIndex)}
                />
            ))}
        </ul>
    );
};
