import { CartCount } from './CartCount';
import { CartInHeaderList } from './CartInHeaderList';
import { CartInHeaderPopover } from './CartInHeaderPopover';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';

const emptyCartTwClassName = [
    'bg-none text-button-primary-text-default border-button-primary-text-default',
    'group-hover:bg-button-primary-bg-hovered group-hover:text-button-primary-text-hovered group-hover:border-button-primary-border-hovered',
    'group-active:bg-button-primary-bg-active group-active:text-button-primary-text-active group-active:border-button-primary-border-active',
];

const nonEmptyCartTwClassName = [
    'bg-button-primary-bg-default text-button-primary-text-default border-button-primary-border-default',
    'group-hover:bg-button-primary-bg-hovered group-hover:text-button-primary-text-hovered group-hover:border-button-primary-border-hovered',
    'group-active:bg-button-primary-bg-active group-active:text-button-primary-text-active group-active:border-button-primary-border-active',
];

export const CartInHeader: FC = ({ className }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);

    const [isActive, setIsActive] = useState(false);
    const isActiveDelayed = useDebounce(isActive, 200);
    const isDesktop = useMediaMin('vl');

    const isPriceVisibleOrEmtpyCart = isPriceVisible(cart?.totalItemsPrice.priceWithVat) || !cart?.items.length;

    const handleOnKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
        if (e.key === 'Enter') {
            setIsActive(true);
        }

        if (e.key === 'Escape') {
            setIsActive(false);
        }
    };

    return (
        <>
            <div
                aria-expanded={isActive}
                aria-haspopup="menu"
                aria-label={t('Show cart popup')}
                data-tid={TIDs.header_cart}
                role="button"
                tabIndex={!cart?.items.length ? -1 : 0}
                title={t('Cart')}
                className={twMergeCustom(
                    'vl:flex group relative outline-none',
                    isActive && 'z-aboveOverlay',
                    className,
                )}
                onClick={() => !isDesktop && setIsActive(!isActive)}
                onKeyDown={(e) => handleOnKeyDown(e)}
                onMouseEnter={() => isDesktop && setIsActive(true)}
                onMouseLeave={() => isDesktop && setIsActive(false)}
                onTouchEnd={(e) => {
                    if (!isActive) {
                        e.preventDefault();
                        setIsActive(!isActive);
                    }
                }}
            >
                {isCartFetchingOrUnavailable && (
                    <Loader
                        className={twJoin(
                            'z-overlay absolute inset-0 flex h-full w-full items-center',
                            'bg-background-more justify-center rounded-lg py-2 opacity-50',
                        )}
                    />
                )}

                <ExtendedNextLink
                    href={cartUrl}
                    skeletonType="cart"
                    tabIndex={-1}
                    tid={TIDs.header_cart_link}
                    className={twJoin(
                        'vl:flex hidden h-11 cursor-pointer items-center justify-center gap-x-3 rounded-lg border px-3 no-underline transition-all group-hover:shadow-lg hover:no-underline',
                        'group-focus-visible:text-text-default group-focus-visible:bg-orange-500',
                        cart?.items.length ? nonEmptyCartTwClassName : emptyCartTwClassName,
                        !isPriceVisible(cart?.totalItemsPrice.priceWithVat) && cart?.items.length
                            ? 'min-w-14'
                            : 'min-w-[151px]',
                    )}
                >
                    <span className="relative flex">
                        <CartIcon className="size-6" />
                        {!!cart?.items.length && <CartCount>{cart.items.length}</CartCount>}
                    </span>

                    {isPriceVisibleOrEmtpyCart && (
                        <span className={twJoin('font-secondary vl:block hidden text-sm font-bold')}>
                            {cart?.items.length
                                ? formatPrice(cart.totalItemsPrice.priceWithVat, {
                                      explicitZero: true,
                                  })
                                : t('Empty')}
                        </span>
                    )}
                </ExtendedNextLink>

                <div
                    aria-controls="cart-popup"
                    aria-expanded={isActive}
                    aria-haspopup="menu"
                    aria-label={t('Show cart popup')}
                    role="button"
                    tabIndex={-1}
                    title={t('Cart')}
                    className={twJoin(
                        'vl:hidden flex h-full w-full cursor-pointer items-center justify-center rounded-md border p-3 text-lg no-underline transition-colors hover:no-underline',
                        'border-button-primary-border-default bg-button-primary-bg-default text-button-primary-text-default',
                        isActiveDelayed &&
                            'hover:border-button-primary-border-hovered hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered',
                        'active:border-button-primary-border-active active:bg-button-primary-bg-active active:text-button-primary-text-active',
                        'group-focus-visible:text-text-default group-focus-visible:bg-orange-500',
                    )}
                    onClick={() => setIsActive(!isActive)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                            setIsActive(!isActive);
                        }
                    }}
                >
                    <CartIcon className="size-6" />
                    <CartCount>{cart?.items.length ?? 0}</CartCount>
                </div>

                <Drawer isActive={isActive} setIsActive={setIsActive} title={t('Cart')}>
                    <CartInHeaderList />
                </Drawer>

                <CartInHeaderPopover isActive={isActiveDelayed} isCartEmpty={!cart?.items.length}>
                    <CartInHeaderList />
                </CartInHeaderPopover>
            </div>

            <Overlay isActive={isActiveDelayed} onClick={() => setIsActive(false)} />
        </>
    );
};
