import { HeaderContact } from './Contact/HeaderContact';
import { Logo } from './Logo/Logo';
import { MenuIconicItem, MenuIconicItemLink } from './MenuIconic/MenuIconicElements';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
// import dynamic from 'next/dynamic';
import { twJoin } from 'tailwind-merge';

const MobileMenuPlaceholder: FC = () => {
    const { t } = useTranslation();

    return (
        <div className={twJoin('flex h-10 w-full cursor-pointer items-center rounded bg-orangeLight p-3')}>
            <div className="flex w-4 items-center justify-center">
                <MenuIcon className="w-4" />
            </div>

            <span className="ml-1 w-7 text-xs">{t('Menu')}</span>
        </div>
    );
};

const MenuIconicPlacehodler: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl, customerUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist', '/customer'],
        url,
    );

    const isUserLoggedIn = useIsUserLoggedIn();

    return (
        <ul className="flex items-center gap-1">
            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={storesUrl} type="stores">
                    <MarkerIcon className="w-4 text-white" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="relative">
                {isUserLoggedIn ? (
                    <div className="group">
                        <MenuIconicItemLink
                            className="rounded-t p-3 group-hover:bg-white group-hover:text-dark max-lg:hidden"
                            href={customerUrl}
                        >
                            <UserIcon className="w-4 text-white group-hover:text-dark" />
                            {t('My account')}
                        </MenuIconicItemLink>
                    </div>
                ) : (
                    <MenuIconicItemLink>
                        <UserIcon className="w-5 lg:w-4" />
                        <span className="hidden lg:inline-block">{t('Login')}</span>
                    </MenuIconicItemLink>
                )}
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')} type="comparison">
                    <CompareIcon className="w-4 text-white" />
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')} type="wishlist">
                    <HeartIcon className="w-4 text-white" isFull={false} />
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};

const AutocompleteSearchPlaceholder: FC = () => {
    const { t } = useTranslation();

    return (
        <div className="relative flex w-full transition-all">
            <div className="relative w-full">
                <input
                    autoComplete="off"
                    className="peer mb-0 h-12 w-full rounded border-2 border-white bg-white pr-20 pl-4 text-dark placeholder:text-grey placeholder:opacity-100 max-vl:border-primaryLight"
                    placeholder={t("Type what you're looking for")}
                    type="search"
                    value=""
                />

                <button
                    className="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer border-none"
                    title={t('Search')}
                    type="submit"
                >
                    <SearchIcon className="w-5" />
                </button>
            </div>
        </div>
    );
};

const CartPlaceholder: FC = () => {
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const formatPrice = useFormatPrice();

    return (
        <div className="group relative lg:flex order-3 vl:order-4">
            <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded bg-greyLighter py-2 opacity-50" />

            <ExtendedNextLink
                href={cartUrl}
                className={twJoin(
                    'hidden items-center gap-x-4 rounded bg-orangeLight py-4 pr-2 pl-4 text-black no-underline transition-all hover:text-black hover:no-underline group-hover:rounded-b-none group-hover:bg-white group-hover:shadow-lg lg:flex',
                )}
            >
                <span className="relative flex text-lg">
                    <CartIcon className="w-6 lg:w-5" />
                    <span className="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-xs font-bold leading-normal text-white lg:-top-2 lg:-right-2">
                        0
                    </span>
                </span>
                <span className="hidden text-sm font-bold lg:block">
                    {formatPrice(0, {
                        explicitZero: true,
                    })}
                </span>
            </ExtendedNextLink>

            <div className="flex cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink
                    className="relative flex h-full w-full items-center justify-center p-3 text-white no-underline transition-colors hover:text-white hover:no-underline"
                    href={cartUrl}
                >
                    <CartIcon className="w-6 text-white" />
                    <span className="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-xs font-bold leading-normal text-white lg:-top-2 lg:-right-2">
                        0
                    </span>
                </ExtendedNextLink>
            </div>
        </div>
    );
};

// const AutocompleteSearch = dynamic(
//     () => import('./AutocompleteSearch/AutocompleteSearch').then((component) => component.AutocompleteSearch),
//     {
//         ssr: false,
//         loading: () => <AutocompleteSearchPlaceholder />,
//     },
// );

// const MenuIconic = dynamic(() => import('./MenuIconic/MenuIconic').then((component) => component.MenuIconic), {
//     ssr: false,
//     loading: () => <MenuIconicPlacehodler />,
// });

// const MobileMenu = dynamic(() => import('./MobileMenu/MobileMenu').then((component) => component.MobileMenu), {
//     ssr: false,
//     loading: () => <MobileMenuPlaceholder />,
// });

// const Cart = dynamic(() => import('./Cart/Cart').then((component) => component.Cart), {
//     ssr: false,
//     loading: () => <CartPlaceholder />,
// });

type HeaderProps = {
    simpleHeader?: boolean;
};

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    return (
        <div className="flex flex-wrap items-center gap-y-3 py-3 lg:gap-x-7 lg:pb-5 lg:pt-6">
            <Logo />

            {simpleHeader ? (
                <HeaderContact />
            ) : (
                <>
                    <div className="order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full vl:order-2 vl:flex-1">
                        <AutocompleteSearchPlaceholder />
                    </div>

                    <div className="order-2 flex">
                        <MenuIconicPlacehodler />
                    </div>

                    <div className="order-4 ml-3 flex cursor-pointer items-center justify-center text-lg lg:hidden">
                        <MobileMenuPlaceholder />
                    </div>

                    <CartPlaceholder />
                </>
            )}
        </div>
    );
};
