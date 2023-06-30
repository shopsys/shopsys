import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ListItem } from './ListItem';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { useCurrentCart } from 'connectors/cart/Cart';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useRemoveFromCart } from 'hooks/cart/useRemoveFromCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { twJoin } from 'tailwind-merge';
import { GtmProductListNameType } from 'types/gtm/enums';
import { twMergeCustom } from 'utils/twMerge';

const TEST_IDENTIFIER = 'layout-header-cart-';

export const Cart: FC = ({ className }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const { cart, isCartEmpty, isFetching } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const loginLoading = usePersistStore((store) => store.loginLoading);
    const [removeItemFromCart, isRemovingItem] = useRemoveFromCart(GtmProductListNameType.cart);
    const isLoading = isFetching || loginLoading !== 'not-loading';

    return (
        <div className={twMergeCustom('group relative lg:flex', className)}>
            {isLoading && (
                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-xl bg-greyLighter py-2 opacity-50" />
            )}

            <ExtendedNextLink href={cartUrl} passHref type="static">
                <a
                    className={twJoin(
                        'hidden items-center rounded-xl bg-orangeLight py-4 pr-2 pl-4 text-black no-underline transition-all hover:text-black hover:no-underline group-hover:rounded-b-none group-hover:bg-white group-hover:shadow-lg lg:flex',
                    )}
                    data-testid={TEST_IDENTIFIER + 'block'}
                >
                    <span className="relative flex text-lg">
                        <Icon iconType="icon" icon="Cart" className="w-5" />
                        <CartCount dataTestId={TEST_IDENTIFIER + 'itemcount'}>{cart?.items.length ?? 0}</CartCount>
                    </span>
                    <span
                        className="ml-4 hidden text-sm font-bold lg:block"
                        data-testid={TEST_IDENTIFIER + 'totalprice'}
                    >
                        {formatPrice(cart?.totalItemsPrice.priceWithVat ?? 0, {
                            explicitZero: true,
                        })}
                    </span>
                </a>
            </ExtendedNextLink>
            <div
                className={twJoin(
                    'pointer-events-none absolute top-full right-0 z-cart hidden origin-top-right scale-75 transition-all group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 lg:block lg:rounded-xl lg:rounded-tr-none lg:bg-white lg:opacity-0 lg:shadow-md',
                    !isCartEmpty ? 'lg:w-[510px] lg:px-5 lg:pt-1 lg:pb-6' : 'lg:w-[400px] lg:py-4',
                )}
                data-testid={TEST_IDENTIFIER + 'detail'}
            >
                {!isCartEmpty ? (
                    <>
                        <ul className="relative m-0 flex max-h-96 w-full list-none flex-col overflow-y-auto p-0">
                            {isRemovingItem && <LoaderWithOverlay className="w-16" />}
                            {cart?.items.map((cartItem, listIndex) => (
                                <ListItem
                                    key={cartItem.uuid}
                                    cartItem={cartItem}
                                    onItemRemove={() => removeItemFromCart(cartItem, listIndex)}
                                />
                            ))}
                        </ul>
                        <div className="flex w-full justify-end pt-5">
                            <Button
                                size="small"
                                onClick={() => router.push(cartUrl)}
                                dataTestId={TEST_IDENTIFIER + 'button'}
                            >
                                {t('Go to cart')}
                            </Button>
                        </div>
                    </>
                ) : (
                    <div className="relative flex h-20 items-center justify-between px-5">
                        <span className="text-dark">{t('Your cart is currently empty.')}</span>
                        <img
                            className="h-20 overflow-hidden"
                            src="/images/empty-cart-small.png"
                            alt="empty cart icon"
                        />
                    </div>
                )}
            </div>
            <div className="flex h-10 w-10 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink href={cartUrl} passHref type="static">
                    <a className="relative flex h-full w-full items-center justify-center text-white no-underline transition-colors hover:text-white hover:no-underline">
                        <Icon iconType="icon" icon="Cart" className="w-5 text-white" />
                        <CartCount>{cart?.items.length ?? 0}</CartCount>
                    </a>
                </ExtendedNextLink>
            </div>
        </div>
    );
};

const CartCount: FC = ({ children, dataTestId }) => (
    <span
        className="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-xs font-bold text-white lg:-top-2 lg:-right-2"
        data-testid={dataTestId}
    >
        {children}
    </span>
);
