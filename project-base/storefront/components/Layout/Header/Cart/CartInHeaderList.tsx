import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { FreeTransportRange } from 'components/Blocks/FreeTransport/FreeTransportRange';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useFocusTrap } from 'utils/useFocusTrap';
import { CartInHeaderListItem } from './CartInHeaderListItem';

type CartInHeaderListProps = {
    hideFocusTrap?: boolean;
    isDrawer?: boolean;
};

export const CartInHeaderList: FC<CartInHeaderListProps> = ({ hideFocusTrap, isDrawer }) => {
    const { t } = useTranslation();
    const { cart } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);
    const contentRef = useRef<HTMLDivElement>(null);
    const scrollContainerRef = useRef<HTMLDivElement>(null);
    const [hasScrollableItems, setHasScrollableItems] = useState(false);

    useFocusTrap(hideFocusTrap ? undefined : contentRef);

    useEffect(() => {
        const scrollContainer = scrollContainerRef.current;

        if (!scrollContainer) {
            setHasScrollableItems(false);

            return undefined;
        }

        const updateScrollableItems = () => {
            setHasScrollableItems(scrollContainer.scrollHeight > scrollContainer.clientHeight);
        };

        updateScrollableItems();

        if (typeof ResizeObserver === 'undefined') {
            window.addEventListener('resize', updateScrollableItems);

            return () => window.removeEventListener('resize', updateScrollableItems);
        }

        const resizeObserver = new ResizeObserver(updateScrollableItems);
        resizeObserver.observe(scrollContainer);

        if (scrollContainer.firstElementChild) {
            resizeObserver.observe(scrollContainer.firstElementChild);
        }

        window.addEventListener('resize', updateScrollableItems);

        return () => {
            resizeObserver.disconnect();
            window.removeEventListener('resize', updateScrollableItems);
        };
    }, [cart?.items.length]);

    if (!cart?.items.length) {
        return <p className="text-center">{t('Your cart is currently empty.')}</p>;
    }

    return (
        <div className={twJoin(isDrawer && 'flex min-h-0 flex-1 flex-col')} ref={contentRef}>
            {isRemovingFromCart && <LoaderWithOverlay className="w-16" overlayClassName="rounded-xl" />}
            <div
                ref={scrollContainerRef}
                className={twJoin(
                    'w-full overflow-y-auto overflow-x-hidden md:w-127',
                    isDrawer ? 'min-h-0 flex-1' : 'max-h-[50dvh]',
                    hasScrollableItems && 'pr-2',
                )}
            >
                <ul className="relative m-0 flex list-none flex-col p-0">
                    {cart.items.map((cartItem, listIndex) => (
                        <CartInHeaderListItem
                            key={cartItem.uuid}
                            cartItem={cartItem}
                            isRemovingFromCart={isRemovingFromCart}
                            listIndex={listIndex}
                            onRemoveFromCart={() => removeFromCart(cartItem, listIndex)}
                        />
                    ))}
                </ul>
            </div>
            <div className="flex shrink-0 items-center justify-between gap-4 pt-5">
                <div className="vl:max-w-75 text-center md:text-left">
                    <FreeTransportRange />
                </div>

                <LinkButton
                    aria-label={t('Go to cart page', { ns: 'accessibility' })}
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
