import { CartInHeaderListItem } from './CartInHeaderListItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { EmptyCartIcon } from 'components/Basic/Icon/EmptyCartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
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
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';

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
    const [isHovered, setIsHovered] = useState(false);

    const isPriceVisibleOrEmtpyCart = isPriceVisible(cart?.totalItemsPrice.priceWithVat) || !cart?.items.length;

    return (
        <div
            className={twMergeCustom('group relative lg:flex', className)}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            {isCartFetchingOrUnavailable && (
                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded bg-backgroundMore py-2 opacity-50" />
            )}

            <ExtendedNextLink
                href={cartUrl}
                tid={TIDs.header_cart_link}
                className={twJoin(
                    'min-w-14 hidden items-center gap-x-3 rounded h-12 pr-2 pl-4 no-underline transition-all hover:no-underline group-hover:rounded-b-none group-hover:shadow-lg lg:flex border',
                    cart?.items.length ? nonEmptyCartTwClassName : emptyCartTwClassName,
                )}
            >
                <span className="relative flex text-lg">
                    <CartIcon className="w-6 lg:w-5" />
                    {!!cart?.items.length && <CartCount>{cart.items.length}</CartCount>}
                </span>

                {isPriceVisibleOrEmtpyCart && (
                    <span className="hidden text-sm font-semibold lg:block">
                        {cart?.items.length
                            ? formatPrice(cart.totalItemsPrice.priceWithVat, {
                                  explicitZero: true,
                              })
                            : t('Empty')}
                    </span>
                )}
            </ExtendedNextLink>

            <div
                className={twJoin(
                    'pointer-events-none absolute top-full right-0 z-cart hidden origin-top-right p-5 scale-75 transition-all',
                    'group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100',
                    'lg:block lg:rounded lg:rounded-tr-none lg:bg-background lg:opacity-0 lg:shadow-md',
                    !cart?.items.length
                        ? 'lg:flex lg:w-96 lg:flex-nowrap lg:items-center lg:justify-between'
                        : 'lg:w-[510px]',
                )}
            >
                {isHovered && (
                    <>
                        {cart?.items.length ? (
                            <>
                                <ul className="relative m-0 flex max-h-96 w-full list-none flex-col overflow-y-auto p-0">
                                    {isRemovingFromCart && <LoaderWithOverlay className="w-16" />}
                                    {cart.items.map((cartItem, listIndex) => (
                                        <CartInHeaderListItem
                                            key={cartItem.uuid}
                                            cartItem={cartItem}
                                            onRemoveFromCart={() => removeFromCart(cartItem, listIndex)}
                                        />
                                    ))}
                                </ul>
                                <div className="flex w-full justify-end pt-5">
                                    <Button size="small" onClick={() => router.push(cartUrl)}>
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

            <div className="flex cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink
                    href={cartUrl}
                    className={twJoin(
                        'relative flex h-full w-full items-center justify-center p-3 no-underline transition-colors hover:no-underline border rounded-lg',
                        'bg-actionPrimaryBackground text-actionPrimaryText border-actionPrimaryBorder',
                        'hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered hover:border-actionPrimaryBorderHovered',
                        'active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive active:border-actionPrimaryBorderActive',
                    )}
                >
                    <CartIcon className="w-6" />
                    <CartCount>{cart?.items.length ?? 0}</CartCount>
                </ExtendedNextLink>
            </div>
        </div>
    );
};

const CartCount: FC = ({ children }) => (
    <span className="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-backgroundAccent text-xs font-bold leading-normal text-textInverted lg:-top-2 lg:-right-2">
        {children}
    </span>
);
