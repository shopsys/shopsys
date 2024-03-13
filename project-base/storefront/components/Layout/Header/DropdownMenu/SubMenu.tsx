import { DropdownMenuContext } from './DropdownMenuContext';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useComparison } from 'hooks/productLists/comparison/useComparison';
import { useWishlist } from 'hooks/productLists/wishlist/useWishlist';
import useTranslation from 'next-translate/useTranslation';
import { useContext } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';

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
        <div className="mt-5 flex flex-col">
            <SubMenuItem href={storesUrl} type="stores">
                {t('Stores')}
            </SubMenuItem>
            <SubMenuItem href={productComparisonUrl} type="comparison">
                {t('Comparison')}
                {!!comparison?.products.length && <span>&nbsp;({comparison.products.length})</span>}
            </SubMenuItem>
            <SubMenuItem href={wishlistUrl} type="wishlist">
                {t('Wishlist')}
                {!!wishlist?.products.length && <span>&nbsp;({wishlist.products.length})</span>}
            </SubMenuItem>

            {isUserLoggedIn ? (
                <SubMenuItem onClick={logout}>{t('Logout')}</SubMenuItem>
            ) : (
                <SubMenuItem href={loginUrl}>{t('Sign in')}</SubMenuItem>
            )}
        </div>
    );
};

type SubMenuItemProps = {
    onClick?: () => void;
    href?: string;
    type?: PageType;
};

const SubMenuItem: FC<SubMenuItemProps> = ({ children, onClick, href, type }) => {
    const { onMenuToggleHandler } = useContext(DropdownMenuContext);

    if (href) {
        return (
            <ExtendedNextLink
                passHref
                className="mb-5 px-8 text-sm text-dark no-underline"
                href={href}
                type={type}
                onClick={onMenuToggleHandler}
            >
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <a className="mb-5 px-8 text-sm text-dark no-underline" onClick={onClick}>
            {children}
        </a>
    );
};
