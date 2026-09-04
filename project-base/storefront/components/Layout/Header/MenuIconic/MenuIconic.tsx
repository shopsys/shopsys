import { CountBadge } from 'components/Basic/CountBadge/CountBadge';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { StoreIcon } from 'components/Basic/Icon/StoreIcon';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductListTypeEnum } from 'graphql/types';
import { ReactElement } from 'react';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useProductListCount } from 'utils/productLists/useProductListCount';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticated } from './MenuIconicItemUserAuthenticated';
import { MenuIconicItemUserUnauthenticated } from './MenuIconicItemUserUnauthenticated';

export type MenuIconicProps = {
    isCompact?: boolean;
    loginFormName?: string;
    shouldUseLocalUserMenuState?: boolean;
};

export const MenuIconic: FC<MenuIconicProps> = ({
    isCompact,
    loginFormName = 'header-login-form',
    shouldUseLocalUserMenuState,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    const comparisonCount = useProductListCount(TypeProductListTypeEnum.Comparison);
    const wishlistCount = useProductListCount(TypeProductListTypeEnum.Wishlist);
    const isUserLoggedIn = useIsUserLoggedIn();

    const menuCountTwClass = 'absolute -right-3 -top-2 bg-icon-accent-red text-text-inverted lg:top-[-6.5px]';
    const userPopoverTopClassName = isCompact ? 'top-10' : undefined;

    return (
        <nav aria-label={t('User tools navigation', { ns: 'accessibility' })}>
            <ul className="flex lg:gap-7">
                <MenuIconicItem className="flex max-lg:hidden">
                    <MenuIconicTooltip isEnabled={isCompact} label={t('Stores')}>
                        <MenuIconicItemLink
                            ariaLabel={t('Go to stores page', { ns: 'accessibility' })}
                            href={storesUrl}
                            tid={TIDs.header_stores_link}
                            title={isCompact ? undefined : t('Stores page')}
                            type="stores"
                        >
                            <StoreIcon className="size-6" />
                            {!isCompact && t('Stores')}
                        </MenuIconicItemLink>
                    </MenuIconicTooltip>
                </MenuIconicItem>

                <MenuIconicItem>
                    <MenuIconicTooltip isEnabled={isCompact} label={t('Comparison')}>
                        <MenuIconicItemLink
                            ariaLabel={t('Go to comparison page', { ns: 'accessibility' })}
                            href={productComparisonUrl}
                            tid={TIDs.header_comparison_link}
                            title={isCompact ? undefined : t('Comparison page')}
                            type="comparison"
                        >
                            <div className="relative">
                                <CompareIcon className="size-6" />
                                {!!comparisonCount && (
                                    <CountBadge className={menuCountTwClass}>{comparisonCount}</CountBadge>
                                )}
                            </div>
                            {!isCompact && <span className="max-lg:hidden">{t('Comparison')}</span>}
                        </MenuIconicItemLink>
                    </MenuIconicTooltip>
                </MenuIconicItem>

                <MenuIconicItem>
                    <MenuIconicTooltip isEnabled={isCompact} label={t('Wishlist')}>
                        <MenuIconicItemLink
                            ariaLabel={t('Go to wishlist page', { ns: 'accessibility' })}
                            href={wishlistUrl}
                            title={isCompact ? undefined : t('Wishlist page')}
                            type="wishlist"
                        >
                            <div className="relative">
                                <HeartIcon className="size-6" />
                                {!!wishlistCount && (
                                    <CountBadge className={menuCountTwClass}>{wishlistCount}</CountBadge>
                                )}
                            </div>
                            {!isCompact && <span className="max-lg:hidden">{t('Wishlist')}</span>}
                        </MenuIconicItemLink>
                    </MenuIconicTooltip>
                </MenuIconicItem>

                <MenuIconicItem>
                    {isUserLoggedIn ? (
                        <MenuIconicItemUserAuthenticated
                            shouldShowLabel={!isCompact}
                            shouldUseLocalUserMenuState={shouldUseLocalUserMenuState}
                            userPopoverTopClassName={userPopoverTopClassName}
                        />
                    ) : (
                        <MenuIconicItemUserUnauthenticated
                            loginFormName={loginFormName}
                            shouldShowLabel={!isCompact}
                            userPopoverTopClassName={userPopoverTopClassName}
                        />
                    )}
                </MenuIconicItem>
            </ul>
        </nav>
    );
};

const MenuIconicTooltip: FC<{ children: ReactElement; isEnabled?: boolean; label: string }> = ({
    children,
    isEnabled,
    label,
}) => {
    if (!isEnabled) {
        return children;
    }

    return (
        <Tooltip label={label} placement="bottom">
            <span className="flex">{children}</span>
        </Tooltip>
    );
};
