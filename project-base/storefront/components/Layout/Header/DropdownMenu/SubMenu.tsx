import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useWishlist } from 'hooks/useWishlist';
import { useComparison } from 'hooks/comparison/useComparison';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-submenu';

export const SubMenu: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const isUserLoggedIn = !!useCurrentCustomerData();
    const [storesUrl, loginUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/login', '/product-comparison', '/wishlist'],
        url,
    );
    const { logout } = useAuth();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <div className="mt-5 flex flex-col" data-testid={TEST_IDENTIFIER}>
            <SubMenuItem href={storesUrl} dataTestId={TEST_IDENTIFIER + '-1'}>
                {t('Stores')}
            </SubMenuItem>
            <SubMenuItem href={productComparisonUrl} dataTestId={TEST_IDENTIFIER + '-3'}>
                {t('Comparison')}
                {!!comparison?.products.length && <span>&nbsp;({comparison.products.length})</span>}
            </SubMenuItem>
            <SubMenuItem href={wishlistUrl} dataTestId={TEST_IDENTIFIER + '-4'}>
                {t('Wishlist')}
                {!!wishlist?.products.length && <span>&nbsp;({wishlist.products.length})</span>}
            </SubMenuItem>

            {isUserLoggedIn ? (
                <SubMenuItem onClick={logout}>{t('Logout')}</SubMenuItem>
            ) : (
                <SubMenuItem href={loginUrl} dataTestId={TEST_IDENTIFIER + '-2'}>
                    {t('Sign in')}
                </SubMenuItem>
            )}
        </div>
    );
};

const SubMenuItem: FC<{ onClick?: () => void; href?: string }> = ({ children, dataTestId, onClick, href }) => {
    if (href) {
        return (
            <ExtendedNextLink href={href} passHref type="static" className="mb-5 px-8 text-sm text-dark no-underline">
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <a className="mb-5 px-8 text-sm text-dark no-underline" onClick={onClick} data-testid={dataTestId}>
            {children}
        </a>
    );
};
