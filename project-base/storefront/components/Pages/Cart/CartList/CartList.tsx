import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { CartProductGifts } from 'components/Pages/Cart/CartProductGifts';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useState } from 'react';
import { groupCartItemsWithGifts } from 'utils/cart/groupCartItemsWithGifts';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { CartListItem } from './CartListItem';

type CartListProps = {
    items: TypeCartFragment['items'];
    removedGifts: TypeCartFragment['removedGifts'];
};

export const CartList: FC<CartListProps> = ({ items: cartItems, removedGifts }) => {
    const { t } = useTranslation();
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);
    const { addToCart, isAddingToCart } = useAddToCart(GtmMessageOriginType.cart, GtmProductListNameType.cart);
    const [cartAnnouncement, setCartAnnouncement] = useState('');

    const productRows = groupCartItemsWithGifts(cartItems, removedGifts);

    const removeCartItem = (cartItem: TypeCartItemFragment, listIndex: number) => {
        setCartAnnouncement(
            t('{{ productName }} was removed from cart.', {
                ns: 'accessibility',
                productName: cartItem.product.fullName,
            }),
        );
        removeFromCart(cartItem, listIndex);
    };

    return (
        <section aria-labelledby="cart-items-heading">
            <h2 className="sr-only" id="cart-items-heading">
                {t('Shopping cart with {{count}} items', { count: cartItems.length })}
            </h2>

            <ul aria-label={t('Cart items list', { ns: 'accessibility' })} className="flex flex-col gap-4">
                {(isRemovingFromCart || isAddingToCart) && <LoaderWithOverlay isFullScreen className="w-16" />}

                {productRows.map(({ cartItem, listIndex, gifts }) => (
                    <CartListItem
                        key={cartItem.uuid}
                        isRemovingFromCart={isRemovingFromCart}
                        item={cartItem}
                        listIndex={listIndex}
                        giftsContent={
                            gifts.length > 0 && (
                                <CartProductGifts
                                    gifts={gifts}
                                    isRemovingGift={isRemovingFromCart}
                                    parentCartItemUuid={cartItem.uuid}
                                    onRemoveGift={removeCartItem}
                                />
                            )
                        }
                        onAddToCart={addToCart}
                        onRemoveFromCart={() => removeCartItem(cartItem, listIndex)}
                    />
                ))}
            </ul>

            <span aria-atomic="true" aria-live="polite" className="sr-only">
                {cartAnnouncement}
            </span>
        </section>
    );
};
