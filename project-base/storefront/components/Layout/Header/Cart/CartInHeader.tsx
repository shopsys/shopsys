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
                    'group relative lg:flex lg:pb-[10px] lg:mb-[-10px]',
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
                        'hidden items-center gap-x-3 rounded-lg h-11 pr-2 pl-4 no-underline transition-all hover:no-underline group-hover:shadow-lg lg:flex border cursor-pointer',
                        cart?.items.length ? nonEmptyCartTwClassName : emptyCartTwClassName,
                        !isPriceVisible(cart?.totalItemsPrice.priceWithVat) && cart?.items.length
                            ? 'min-w-14'
                            : 'min-w-[132px]',
                    )}
                    onClick={() => {
                        setIsClicked(!isClicked);
                        setIsClicked(!isHovered);
                    }}
                >
                    <span className="relative flex text-lg">
                        <CartIcon className="w-6 lg:w-5" />
                        {!!cart?.items.length && <CartCount>{cart.items.length}</CartCount>}
                    </span>
                    {isPriceVisibleOrEmtpyCart && (
                        <span
                            className={twJoin(
                                'hidden text-sm font-semibold lg:block',
                                !cart?.items.length && 'lg:w-full lg:text-center',
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
                            'relative flex h-full w-full items-center justify-center p-3 no-underline transition-colors hover:no-underline border rounded-lg',
                            'bg-actionPrimaryBackground text-actionPrimaryText border-actionPrimaryBorder',
                            isHoveredDelayed &&
                                'hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered hover:border-actionPrimaryBorderHovered',
                            'active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive active:border-actionPrimaryBorderActive',
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
                        'pointer-events-none absolute top-[-12px] right-[-15px] z-cart min-w-[315px] origin-top-right scale-75 transition-all bg-background',
                        'lg:block lg:rounded-lg lg:opacity-0 lg:right-0 lg:top-full lg:h-auto lg:p-5 lg:scale-75',
                        isHoveredDelayed &&
                            'group-hover:pointer-events-auto group-hover:opacity-100 group-hover:scale-100',
                        isClicked &&
                            'opacity-100 scale-100 top-0 right-0 rounded-none h-dvh fixed z-aboveOverlay pointer-events-auto',
                        (isClicked || isHovered) && 'p-5',
                        !cart?.items.length
                            ? 'lg:flex lg:w-96 lg:flex-nowrap lg:items-center lg:justify-between'
                            : 'lg:w-[548px]',
                    )}
                >
                    {(isHovered || isClicked) && (
                        <>
                            <div className="flex flex-row justify-between mb-10 lg:hidden pr-1">
                                <span className="text-base w-full text-center">{t('Cart')}</span>
                                <RemoveIcon
                                    className="w-4 text-borderAccent cursor-pointer"
                                    onClick={() => setIsClicked(false)}
                                />
                            </div>
                            {cart?.items.length ? (
                                <>
                                    <ul className="relative w-[315px] max-h-[78dvh] m-0 flex list-none flex-col overflow-y-auto p-0 lg:w-[510px] lg:max-h-[50dvh] overflow-auto">
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
                                            'flex pt-5 gap-4',
                                            shouldDisplayTransportBar ? 'justify-between' : 'justify-end',
                                        )}
                                    >
                                        <FreeTransportRange />
                                        <Button
                                            className="rounded-lg"
                                            size="small"
                                            onClick={() => router.push(cartUrl)}
                                        >
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
    <span className="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-backgroundAccent text-[10px] font-bold leading-normal text-textInverted lg:-top-2 lg:-right-2">
        {children}
    </span>
);
