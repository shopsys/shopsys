import { CartInHeaderListItem } from './CartInHeaderListItem';
import { EmptyCartIcon } from 'components/Basic/Icon/EmptyCartIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { FreeTransportRange } from 'components/Blocks/FreeTransport/FreeTransportRange';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useFocusTrap } from 'utils/useFocusTrap';

export const CartInHeaderList: FC = () => {
    const { t } = useTranslation();
    const { cart } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);
    const contentRef = useRef<HTMLDivElement>(null);

    useFocusTrap(contentRef);

    if (!cart?.items.length) {
        return (
            <>
                <span>{t('Your cart is currently empty.')}</span>
                <EmptyCartIcon className={twJoin('w-20')} />
            </>
        );
    }

    return (
        <div ref={contentRef}>
            <ul
                className={twJoin(
                    'relative m-0 flex h-full list-none flex-col overflow-y-auto p-0',
                    'overflow-auto md:w-[510px] lg:max-h-[50dvh]',
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
            <div className={twJoin('flex items-center justify-between gap-4 pt-5')}>
                <div className="vl:max-w-[300px] text-center md:text-left">
                    <FreeTransportRange />
                </div>

                <LinkButton
                    aria-label={t('Go to cart page')}
                    className="ml-auto whitespace-nowrap"
                    href={cartUrl}
                    size="small"
                    skeletonType="cart"
                >
                    {t('Go to cart')}
                </LinkButton>
            </div>
        </div>
    );
};
