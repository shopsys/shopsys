import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { CompareIcon, HeartIcon, MarkerIcon } from 'components/Basic/Icon/IconsSvg';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useComparison } from 'hooks/productLists/comparison/useComparison';
import { useWishlist } from 'hooks/productLists/wishlist/useWishlist';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';

const MenuIconicItemUserAuthenticated = dynamic(() =>
    import('components/Layout/Header/MenuIconic/MenuIconicItemUserAuthenticated').then(
        (component) => component.MenuIconicItemUserAuthenticated,
    ),
);

const MenuIconicItemUserUnauthenticated = dynamic(() =>
    import('components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticated').then(
        (component) => component.MenuIconicItemUserUnauthenticated,
    ),
);

export const MenuIconic: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();
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
                    <MenuIconicItemUserAuthenticated className="relative" />
                ) : (
                    <MenuIconicItemUserUnauthenticated />
                )}
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')} type="comparison">
                    <CompareIcon className="w-4 text-white" />
                    {!!comparison?.products.length && <span>{comparison.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')} type="wishlist">
                    <HeartIcon className="w-4 text-white" isFull={!!wishlist?.products.length} />
                    {!!wishlist?.products.length && <span>{wishlist.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};
