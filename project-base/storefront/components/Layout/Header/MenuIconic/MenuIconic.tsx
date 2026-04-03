import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useProductListCount } from 'utils/productLists/useProductListCount';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticated } from './MenuIconicItemUserAuthenticated';
import { MenuIconicItemUserUnauthenticated } from './MenuIconicItemUserUnauthenticated';

export const MenuIconic: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    const comparisonCount = useProductListCount(TypeProductListTypeEnum.Comparison);
    const wishlistCount = useProductListCount(TypeProductListTypeEnum.Wishlist);
    const isUserLoggedIn = useIsUserLoggedIn();

    const menuCountTwClass =
        'absolute -right-3 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-icon-accent-red px-0.5 font-secondary text-xs font-bold leading-normal text-text-inverted lg:-top-[6.5px]';

    return (
        <nav aria-label={t('User tools navigation', { ns: 'accessibility' })}>
            <ul className="flex lg:gap-7">
                <MenuIconicItem className="flex max-lg:hidden">
                    <MenuIconicItemLink
                        aria-label={t('Go to stores page', { ns: 'accessibility' })}
                        href={storesUrl}
                        tid={TIDs.header_stores_link}
                        title={t('Stores page')}
                        type="stores"
                    >
                        <MarkerIcon className="size-6" />
                        {t('Stores')}
                    </MenuIconicItemLink>
                </MenuIconicItem>

                <MenuIconicItem>
                    <MenuIconicItemLink
                        aria-label={t('Go to comparison page', { ns: 'accessibility' })}
                        href={productComparisonUrl}
                        tid={TIDs.header_comparison_link}
                        title={t('Comparison page')}
                        type="comparison"
                    >
                        <div className="relative">
                            <CompareIcon className="size-6" />
                            {!!comparisonCount && <span className={menuCountTwClass}>{comparisonCount}</span>}
                        </div>
                        <span className="max-lg:hidden">{t('Comparison')}</span>
                    </MenuIconicItemLink>
                </MenuIconicItem>

                <MenuIconicItem>
                    <MenuIconicItemLink
                        aria-label={t('Go to wishlist page', { ns: 'accessibility' })}
                        href={wishlistUrl}
                        title={t('Wishlist page')}
                        type="wishlist"
                    >
                        <div className="relative">
                            <HeartIcon className="size-6" />
                            {!!wishlistCount && <span className={menuCountTwClass}>{wishlistCount}</span>}
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
