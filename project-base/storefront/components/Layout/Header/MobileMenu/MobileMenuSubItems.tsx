import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useLogout } from 'utils/auth/useLogout';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type SubMenuProps = {
    onNavigate: () => void;
};

export const SubMenu: FC<SubMenuProps> = ({ onNavigate }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [storesUrl, loginUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/login', '/product-comparison', '/wishlist'],
        url,
    );
    const logout = useLogout();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <div className="flex flex-col mt-auto">
            <SubMenuItem href={storesUrl} type="stores" onClick={onNavigate}>
                {t('Stores')}
            </SubMenuItem>

            <SubMenuItem href={productComparisonUrl} type="comparison" onClick={onNavigate}>
                {t('Comparison')}
                {!!comparison?.products.length && <span>&nbsp;({comparison.products.length})</span>}
            </SubMenuItem>

            <SubMenuItem href={wishlistUrl} type="wishlist" onClick={onNavigate}>
                {t('Wishlist')}
                {!!wishlist?.products.length && <span>&nbsp;({wishlist.products.length})</span>}
            </SubMenuItem>

            {isUserLoggedIn ? (
                <SubMenuItem
                    onClick={() => {
                        onNavigate();
                        logout();
                    }}
                >
                    {t('Logout')}
                </SubMenuItem>
            ) : (
                <SubMenuItem href={loginUrl} onClick={onNavigate}>
                    {t('Sign in')}
                </SubMenuItem>
            )}
        </div>
    );
};

type SubMenuItemProps = {
    href?: string;
    type?: PageType;
    onClick: () => void;
};

const subMenuItemTwClass = 'py-3 text-sm text-dark no-underline';

const SubMenuItem: FC<SubMenuItemProps> = ({ children, onClick, href, type }) => {
    if (href) {
        return (
            <ExtendedNextLink passHref className={subMenuItemTwClass} href={href} type={type} onClick={onClick}>
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <a className={subMenuItemTwClass} onClick={onClick}>
            {children}
        </a>
    );
};
