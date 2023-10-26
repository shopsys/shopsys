import { DropdownMenuContext } from './DropdownMenuContext';
import { ExtendedLinkPageType, ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useComparison } from 'hooks/comparison/useComparison';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useWishlist } from 'hooks/useWishlist';
import useTranslation from 'next-translate/useTranslation';
import { useContext } from 'react';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-submenu';

export const SubMenu: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [storesUrl, loginUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/login', '/product-comparison', '/wishlist'],
        url,
    );
    const { logout } = useAuth();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <div className="mt-5 flex flex-col" data-testid={TEST_IDENTIFIER}>
            <SubMenuItem dataTestId={TEST_IDENTIFIER + '-1'} href={storesUrl} type="stores">
                {t('Stores')}
            </SubMenuItem>
            <SubMenuItem dataTestId={TEST_IDENTIFIER + '-3'} href={productComparisonUrl} type="comparison">
                {t('Comparison')}
                {!!comparison?.products.length && <span>&nbsp;({comparison.products.length})</span>}
            </SubMenuItem>
            <SubMenuItem dataTestId={TEST_IDENTIFIER + '-4'} href={wishlistUrl} type="wishlist">
                {t('Wishlist')}
                {!!wishlist?.products.length && <span>&nbsp;({wishlist.products.length})</span>}
            </SubMenuItem>

            {isUserLoggedIn ? (
                <SubMenuItem onClick={logout}>{t('Logout')}</SubMenuItem>
            ) : (
                <SubMenuItem dataTestId={TEST_IDENTIFIER + '-2'} href={loginUrl}>
                    {t('Sign in')}
                </SubMenuItem>
            )}
        </div>
    );
};

type SubMenuItemProps = {
    onClick?: () => void;
    href?: string;
    type?: ExtendedLinkPageType;
};

const SubMenuItem: FC<SubMenuItemProps> = ({ children, dataTestId, onClick, href, type }) => {
    const { onMenuToggleHandler } = useContext(DropdownMenuContext);

    if (href) {
        return (
            <ExtendedNextLink
                passHref
                className="mb-5 px-8 text-sm text-dark no-underline"
                href={href}
                type={type || 'static'}
                onClick={onMenuToggleHandler}
            >
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <a className="mb-5 px-8 text-sm text-dark no-underline" data-testid={dataTestId} onClick={onClick}>
            {children}
        </a>
    );
};
