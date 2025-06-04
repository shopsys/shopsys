import { CartListItem } from './CartListItem';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';

type CartListProps = {
    items: TypeCartItemFragment[];
};

export const CartList: FC<CartListProps> = ({ items: cartItems }) => {
    const { t } = useTranslation();
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);
    const { addToCart, isAddingToCart } = useAddToCart(GtmMessageOriginType.cart, GtmProductListNameType.cart);

    return (
        <>
            <h2 className="sr-only">{t('Products')}</h2>

            <ul className="flex flex-col gap-4">
                {(isRemovingFromCart || isAddingToCart) && <LoaderWithOverlay className="w-16" />}

                {cartItems.map((cartItem, listIndex) => (
                    <CartListItem
                        key={cartItem.uuid}
                        item={cartItem}
                        listIndex={listIndex}
                        onAddToCart={addToCart}
                        onRemoveFromCart={() => removeFromCart(cartItem, listIndex)}
                    />
                ))}
            </ul>
        </>
    );
};
