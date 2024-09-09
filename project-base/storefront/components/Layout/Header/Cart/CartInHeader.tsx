import { CartInHeaderListItem } from './CartInHeaderListItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { EmptyCartIcon } from 'components/Basic/Icon/EmptyCartIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import FreeTransportRange from 'components/Blocks/FreeTransport/FreeTransportRange';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
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
    const router = useRouter();
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(GtmProductListNameType.cart);
    const [isClicked, setIsClicked] = useState(false);
    const [isHovered, setIsHovered] = useState(false);
    const isHoveredDelayed = useDebounce(isHovered, 200);

    const isPriceVisibleOrEmtpyCart = isPriceVisible(cart?.totalItemsPrice.priceWithVat) || !cart?.items.length;
    const { width } = useGetWindowSize();
    const isDesktop = width > desktopFirstSizes.tablet;

    const shouldDisplayTransportBar = cart?.remainingAmountWithVatForFreeTransport !== null && cart?.items.length;

    return (
        <>
            <div
                className={twMergeCustom(
                    'group relative lg:flex',
                    !isCartFetchingOrUnavailable && 'lg:-mb-2.5 lg:pb-2.5',
                    (isClicked || isHovered) && 'z-aboveOverlay',
                    className,
                )}
                onMouseEnter={() => isDesktop && setIsHovered(true)}
                onMouseLeave={() => isDesktop && setIsHovered(false)}
            >
                {isCartFetchingOrUnavailable && (
                    <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded bg-backgroundMore py-2 opacity-50" />
                )}
                <ExtendedNextLink
                    href={cartUrl}
                    tid={TIDs.header_cart_link}
                    className={twJoin(
                        'hidden h-11 cursor-pointer items-center gap-x-2 rounded-lg border px-3 no-underline transition-all hover:no-underline group-hover:shadow-lg lg:flex',
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
                        <span
                            className={twJoin(
                                'hidden font-secondary text-sm font-bold lg:block',
                                !cart?.items.length && 'lg:w-full',
                            )}
                        >
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

                <div
                    className={twMergeCustom(
                        'pointer-events-none absolute right-[-15px] top-[-12px] z-cart min-w-[315px] origin-top-right scale-75 bg-background transition-all',
                        'lg:right-0 lg:top-full lg:block lg:h-auto lg:scale-75 lg:rounded-xl lg:p-5 lg:opacity-0',
                        isHoveredDelayed &&
                            'group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100',
                        isClicked &&
                            'pointer-events-auto fixed right-0 top-0 z-aboveOverlay h-dvh scale-100 rounded-none opacity-100',
                        (isClicked || isHovered) && 'p-5',
                        !cart?.items.length
                            ? 'lg:flex lg:w-96 lg:flex-nowrap lg:items-center lg:justify-between'
                            : 'lg:w-[548px]',
                    )}
                >
                    {(isHovered || isClicked) && (
                        <>
                            <div className="mb-10 flex flex-row justify-between pr-1 lg:hidden">
                                <span className="w-full text-center text-base">{t('Cart')}</span>
                                <RemoveIcon
                                    className="size-4 cursor-pointer text-borderAccent"
                                    onClick={() => setIsClicked(false)}
                                />
                            </div>
                            {cart?.items.length ? (
                                <>
                                    <ul className="relative m-0 flex max-h-[78dvh] w-[315px] list-none flex-col overflow-auto overflow-y-auto p-0 lg:max-h-[50dvh] lg:w-[510px]">
                                        {isRemovingFromCart && <LoaderWithOverlay className="w-16" />}
                                        {cart.items.map((cartItem, listIndex) => (
                                            <CartInHeaderListItem
                                                key={cartItem.uuid}
                                                cartItem={cartItem}
                                                onRemoveFromCart={() => removeFromCart(cartItem, listIndex)}
                                            />
                                        ))}
                                    </ul>
                                    <div
                                        className={twJoin(
                                            'flex items-center gap-4 pt-5',
                                            shouldDisplayTransportBar ? 'justify-between' : 'justify-end',
                                        )}
                                    >
                                        <FreeTransportRange />
                                        <Button className="rounded-lg" onClick={() => router.push(cartUrl)}>
                                            {t('Go to cart')}
                                        </Button>
                                    </div>
                                </>
                            ) : (
                                <>
                                    <span>{t('Your cart is currently empty.')}</span>
                                    <EmptyCartIcon className={twJoin('w-20')} />
                                </>
                            )}
                        </>
                    )}
                </div>
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
