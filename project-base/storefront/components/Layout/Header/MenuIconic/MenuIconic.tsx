import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useComparison } from 'hooks/comparison/useComparison';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useWishlist } from 'hooks/useWishlist';
import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { CompareIcon, HeartIcon, MarkerIcon } from 'components/Basic/Icon/IconsSvg';
import dynamic from 'next/dynamic';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

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
        <ul className="flex items-center gap-1" data-testid={TEST_IDENTIFIER}>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-stores'} className="max-lg:hidden">
                <MenuIconicItemLink href={storesUrl}>
                    <MarkerIcon className="w-4 text-white" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-login'} className="relative">
                {isUserLoggedIn ? (
                    <MenuIconicItemUserAuthenticated dataTestId={TEST_IDENTIFIER + '-login'} className="relative" />
                ) : (
                    <MenuIconicItemUserUnauthenticated dataTestId={TEST_IDENTIFIER + '-login'} />
                )}
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-comparison'} className="max-lg:hidden">
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')}>
                    <CompareIcon className="w-4 text-white" />
                    {!!comparison?.products.length && <span>{comparison.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-wishlist'} className="max-lg:hidden">
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')}>
                    <HeartIcon isFull={!!wishlist?.products.length} className="w-4 text-white" />
                    {!!wishlist?.products.length && <span>{wishlist.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};
