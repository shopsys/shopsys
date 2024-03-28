import { CartListItem } from './CartListItem';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';

type CartListProps = {
    items: TypeCartItemFragment[];
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
