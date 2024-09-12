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
        'flex w-4 h-4 text-white text-[10px] leading-[10px] rounded-full bg-activeIconFull align-center justify-center pt-[4px] absolute -top-1 -right-2';

    return (
        <ul className="flex items-center gap-1 h-12">
            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={storesUrl} type="stores">
                    <MarkerIcon className="w-6" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden">
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')} type="comparison">
                    <div className="relative">
                        <CompareIcon className="w-6" isFull={false} />
                        {!!comparison?.products.length && (
                            <span className={menuCountTwClass}>{comparison.products.length}</span>
                        )}
                    </div>
                    {t('Comparison')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')} type="wishlist">
                    <div className="relative">
                        <HeartIcon className="w-6" isFull={false} />
                        {!!wishlist?.products.length && (
                            <span className={menuCountTwClass}>{wishlist.products.length}</span>
                        )}
                    </div>
                    <span className="max-lg:hidden">{t('Wishlist')}</span>
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="relative">
                {isUserLoggedIn ? (
                    <MenuIconicItemUserAuthenticated className="relative" />
                ) : (
                    <MenuIconicItemUserUnauthenticated />
                )}
            </MenuIconicItem>
        </ul>
    );
};
