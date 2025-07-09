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
        'absolute -right-3 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-icon-accent-red px-0.5 font-secondary text-xs font-bold leading-normal text-text-inverted lg:-top-[6.5px]';

    return (
        <nav aria-label={t('User tools navigation')}>
            <ul className="flex lg:gap-7">
                <MenuIconicItem className="flex max-lg:hidden">
                    <MenuIconicItemLink
                        aria-label={t('Go to stores page')}
                        href={storesUrl}
                        title={t('Stores page')}
                        type="stores"
                    >
                        <MarkerIcon className="size-6" />
                        {t('Stores')}
                    </MenuIconicItemLink>
                </MenuIconicItem>

                <MenuIconicItem>
                    <MenuIconicItemLink
                        aria-label={t('Go to comparison page')}
                        href={productComparisonUrl}
                        title={t('Comparison page')}
                        type="comparison"
                    >
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
                    <MenuIconicItemLink
                        aria-label={t('Go to wishlist page')}
                        href={wishlistUrl}
                        title={t('Wishlist page')}
                        type="wishlist"
                    >
                        <div className="relative">
                            <HeartIcon className="size-6" />
                            {!!wishlist?.products.length && (
                                <span className={menuCountTwClass}>{wishlist.products.length}</span>
                            )}
                        </div>
                        <span className="max-lg:hidden">{t('Wishlist')}</span>
                    </MenuIconicItemLink>
                </MenuIconicItem>

                <MenuIconicItem>
                    {isUserLoggedIn ? <MenuIconicItemUserAuthenticated /> : <MenuIconicItemUserUnauthenticated />}
                </MenuIconicItem>
            </ul>
        </nav>
    );
};
