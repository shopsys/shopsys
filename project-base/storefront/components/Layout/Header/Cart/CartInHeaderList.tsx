import { CartInHeaderListItem } from './CartInHeaderListItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { EmptyCartIcon } from 'components/Basic/Icon/EmptyCartIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import FreeTransportRange from 'components/Blocks/FreeTransport/FreeTransportRange';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const CartInHeaderList: FC = () => {
    const { t } = useTranslation();
    const { cart } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);

    const shouldDisplayTransportBar = cart?.remainingAmountWithVatForFreeTransport !== null && cart?.items.length;

    if (!cart?.items.length) {
        return (
            <>
                <span>{t('Your cart is currently empty.')}</span>
                <EmptyCartIcon className={twJoin('w-20')} />
            </>
        );
    }
    return (
        <>
            <ul
                className={twJoin(
                    'relative m-0 flex max-h-[78dvh] w-[315px] list-none flex-col overflow-y-auto p-0',
                    'overflow-auto lg:max-h-[50dvh] lg:w-[510px]',
                )}
            >
                {isRemovingFromCart && <LoaderWithOverlay className="w-16" />}
                {cart.items.map((cartItem, listIndex) => (
                    <CartInHeaderListItem
                        key={cartItem.uuid}
                        cartItem={cartItem}
                        onRemoveFromCart={() => removeFromCart(cartItem, listIndex)}
                    />
                ))}
            </ul>
            <div className={twJoin('flex gap-4 pt-5', shouldDisplayTransportBar ? 'justify-between' : 'justify-end')}>
                <FreeTransportRange />
                <ExtendedNextLink href={cartUrl} skeletonType="cart">
                    <Button className="rounded-lg" size="small">
                        {t('Go to cart')}
                    </Button>
                </ExtendedNextLink>
            </div>
        </>
    );
};
