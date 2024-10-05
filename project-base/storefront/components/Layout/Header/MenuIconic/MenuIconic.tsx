import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticated } from './MenuIconicItemUserAuthenticated';
import { MenuIconicItemUserUnauthenticated } from './MenuIconicItemUserUnauthenticated';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
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

    const menuCountTwClass =
        'absolute -right-2 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-activeIconFull px-0.5 font-secondary text-[10px] font-bold leading-normal text-textInverted lg:-right-2 lg:-top-[6.5px]';

    return (
        <ul className="flex lg:gap-7">
            <MenuIconicItem className="flex max-lg:hidden">
                <MenuIconicItemLink href={storesUrl} type="stores">
                    <MarkerIcon className="size-6" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')} type="comparison">
                    <div className="relative">
                        <CompareIcon className="size-6" />
                        {!!comparison?.products.length && (
                            <span className={menuCountTwClass}>{comparison.products.length}</span>
                        )}
                    </div>
                    <span className="max-lg:hidden">{t('Comparison')}</span>
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')} type="wishlist">
                    <div className="relative">
                        <HeartIcon className="size-6" />
                        {!!wishlist?.products.length && (
                            <span className={menuCountTwClass}>{wishlist.products.length}</span>
                        )}
                    </div>
                    <span className="max-lg:hidden">{t('Favorites')}</span>
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                {isUserLoggedIn ? (
                    <MenuIconicItemUserAuthenticated className="relative" />
                ) : (
                    <MenuIconicItemUserUnauthenticated />
                )}
            </MenuIconicItem>
        </ul>
    );
};
