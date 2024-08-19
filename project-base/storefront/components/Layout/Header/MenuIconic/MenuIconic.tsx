import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticated } from './MenuIconicItemUserAuthenticated';
import { MenuIconicItemUserUnauthenticated } from './MenuIconicItemUserUnauthenticated';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

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
        <ul className="flex items-center gap-1 h-12">
            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={storesUrl} type="stores">
                    <MarkerIcon className="w-4" />
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
                    <CompareIcon className="w-4" isFull={!!comparison?.products.length} />
                    {!!comparison?.products.length && <span>{comparison.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')} type="wishlist">
                    <HeartIcon
                        className={twJoin('w-6 lg:w-4', wishlist?.products.length ? 'text-activeIconFull' : '')}
                        isFull={!!wishlist?.products.length}
                    />
                    <span className="max-lg:hidden">
                        {!!wishlist?.products.length && <span>{wishlist.products.length}</span>}
                    </span>
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};
