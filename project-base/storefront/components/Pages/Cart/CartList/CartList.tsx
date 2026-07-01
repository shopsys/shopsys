import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useState } from 'react';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { CartListItem } from './CartListItem';

type CartListProps = {
    items: TypeCartItemFragment[];
};

export const CartList: FC<CartListProps> = ({ items: cartItems }) => {
    const { t } = useTranslation();
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);
    const { addToCart, isAddingToCart } = useAddToCart(GtmMessageOriginType.cart, GtmProductListNameType.cart);
    const [cartAnnouncement, setCartAnnouncement] = useState('');

    return (
        <section aria-labelledby="cart-items-heading">
            <h2 className="sr-only" id="cart-items-heading">
                {t('Shopping cart with {{count}} items', { count: cartItems.length })}
            </h2>

            <ul aria-label={t('Cart items list', { ns: 'accessibility' })} className="flex flex-col gap-4">
                {(isRemovingFromCart || isAddingToCart) && <LoaderWithOverlay isFullScreen className="w-16" />}

                {cartItems.map((cartItem, listIndex) => (
                    <CartListItem
                        key={cartItem.uuid}
                        isRemovingFromCart={isRemovingFromCart}
                        item={cartItem}
                        listIndex={listIndex}
                        onAddToCart={addToCart}
                        onRemoveFromCart={() => {
                            setCartAnnouncement(
                                t('{{ productName }} was removed from cart.', {
                                    ns: 'accessibility',
                                    productName: cartItem.product.fullName,
                                }),
                            );
                            removeFromCart(cartItem, listIndex);
                        }}
                    />
                ))}
            </ul>

            <span aria-atomic="true" aria-live="polite" className="sr-only">
                {cartAnnouncement}
            </span>
        </section>
    );
};
