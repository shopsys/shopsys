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
import { desktopFirstSizes } from 'utils/mediaQueries';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useDebounce } from 'utils/useDebounce';

const emptyCartTwClassName = [
    'bg-none text-actionPrimaryText border-actionPrimaryText',
    'group-hover:bg-actionPrimaryBackgroundHovered group-hover:text-actionPrimaryTextHovered group-hover:border-actionPrimaryBorderHovered',
    'group-active:bg-actionPrimaryBackgroundActive group-active:text-actionPrimaryTextActive group-active:border-actionPrimaryBorderActive',
];

const nonEmptyCartTwClassName = [
    'bg-actionPrimaryBackground text-actionPrimaryText border-actionPrimaryBorder',
    'group-hover:bg-actionPrimaryBackgroundHovered group-hover:text-actionPrimaryTextHovered group-hover:border-actionPrimaryBorderHovered',
    'group-active:bg-actionPrimaryBackgroundActive group-active:text-actionPrimaryTextActive group-active:border-actionPrimaryBorderActive',
];

export const CartInHeader: FC = ({ className }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const [isClicked, setIsClicked] = useState(false);
    const [isHovered, setIsHovered] = useState(false);
    const isHoveredDelayed = useDebounce(isHovered, 200);

    const isPriceVisibleOrEmtpyCart = isPriceVisible(cart?.totalItemsPrice.priceWithVat) || !cart?.items.length;
    const { width } = useGetWindowSize();
    const isDesktop = width > desktopFirstSizes.tablet;

    return (
        <>
            <div
                className={twMergeCustom(
                    'group relative lg:flex',
                    (isClicked || isHovered) && 'z-aboveOverlay',
                    className,
                )}
                onMouseEnter={() => isDesktop && setIsHovered(true)}
                onMouseLeave={() => isDesktop && setIsHovered(false)}
            >
                {isCartFetchingOrUnavailable && (
                    <Loader
                        className={twJoin(
                            'absolute inset-0 z-overlay flex h-full w-full items-center',
                            'justify-center rounded-lg bg-backgroundMore py-2 opacity-50',
                        )}
                    />
                )}
                <ExtendedNextLink
                    href={cartUrl}
                    skeletonType="cart"
                    tid={TIDs.header_cart_link}
                    className={twJoin(
                        'hidden h-11 cursor-pointer items-center justify-center gap-x-2 rounded-lg border px-3 no-underline transition-all hover:no-underline group-hover:shadow-lg lg:flex',
                        cart?.items.length ? nonEmptyCartTwClassName : emptyCartTwClassName,
                        !isPriceVisible(cart?.totalItemsPrice.priceWithVat) && cart?.items.length
                            ? 'min-w-14'
                            : 'min-w-[151px]',
                    )}
                    onClick={() => {
                        setIsClicked(!isClicked);
                        setIsClicked(!isHovered);
                    }}
                >
                    <span className="relative flex">
                        <CartIcon className="size-6" />
                        {!!cart?.items.length && <CartCount>{cart.items.length}</CartCount>}
                    </span>
                    {isPriceVisibleOrEmtpyCart && (
                        <span className={twJoin('hidden font-secondary text-sm font-bold lg:block')}>
                            {cart?.items.length
                                ? formatPrice(cart.totalItemsPrice.priceWithVat, {
                                      explicitZero: true,
                                  })
                                : t('Empty')}
                        </span>
                    )}
                </ExtendedNextLink>

                <div className="flex cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                    <div
                        className={twJoin(
                            'relative flex h-full w-full items-center justify-center rounded-md border p-3 no-underline transition-colors hover:no-underline',
                            'border-actionPrimaryBorder bg-actionPrimaryBackground text-actionPrimaryText',
                            isHoveredDelayed &&
                                'hover:border-actionPrimaryBorderHovered hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered',
                            'active:border-actionPrimaryBorderActive active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive',
                        )}
                        onClick={(event) => {
                            event.preventDefault();
                            setIsClicked(!isClicked);
                        }}
                    >
                        <CartIcon className="w-6" />
                        <CartCount>{cart?.items.length ?? 0}</CartCount>
                    </div>
                </div>

                <Drawer className="lg:hidden" isClicked={isClicked} setIsClicked={setIsClicked} title={t('Cart')}>
                    <CartInHeaderList />
                </Drawer>

                <CartInHeaderPopover isCartEmpty={!cart?.items.length} isHovered={isHoveredDelayed}>
                    <CartInHeaderList />
                </CartInHeaderPopover>
            </div>
            <Overlay
                isActive={isHoveredDelayed || isClicked}
                onClick={() => {
                    setIsClicked(false);
                    setIsHovered(false);
                }}
            />
        </>
    );
};

const CartCount: FC = ({ children }) => (
    <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-backgroundAccent px-0.5 font-secondary text-[10px] font-bold leading-normal text-textInverted lg:-right-2 lg:-top-[6.5px]">
        {children}
    </span>
);
