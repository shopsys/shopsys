import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useWishlist } from 'hooks/useWishlist';
import { useComparison } from 'hooks/comparison/useComparison';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-submenu';

export const SubMenu: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const { isUserLoggedIn } = useCurrentUserData();
    const [storesUrl, loginUrl, productsComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/login', '/products-comparison', '/wishlist'],
        url,
    );
    const { logout } = useAuth();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <div className="mt-5 flex flex-col" data-testid={TEST_IDENTIFIER}>
            <ExtendedNextLink href={storesUrl} passHref type="static">
                <SubMenuItem dataTestId={TEST_IDENTIFIER + '-1'}>{t('Stores')}</SubMenuItem>
            </ExtendedNextLink>

            <ExtendedNextLink href={productsComparisonUrl} passHref type="static">
                <SubMenuItem dataTestId={TEST_IDENTIFIER + '-3'}>
                    {t('Comparison')}
                    {!!comparison?.products.length && <span>&nbsp;({comparison.products.length})</span>}
                </SubMenuItem>
            </ExtendedNextLink>

            <ExtendedNextLink href={wishlistUrl} passHref type="static">
                <SubMenuItem dataTestId={TEST_IDENTIFIER + '-4'}>
                    {t('Wishlist')}
                    {!!wishlist?.products.length && <span>&nbsp;({wishlist.products.length})</span>}
                </SubMenuItem>
            </ExtendedNextLink>

            {isUserLoggedIn ? (
                <SubMenuItem onClick={logout}>{t('Logout')}</SubMenuItem>
            ) : (
                <ExtendedNextLink href={loginUrl} passHref type="static">
                    <SubMenuItem dataTestId={TEST_IDENTIFIER + '-2'}>{t('Sign in')}</SubMenuItem>
                </ExtendedNextLink>
            )}
        </div>
    );
};

const SubMenuItem: FC<{ onClick?: () => void }> = ({ children, dataTestId, onClick }) => (
    <a className="mb-5 px-8 text-sm text-dark no-underline" onClick={onClick} data-testid={dataTestId}>
        {children}
    </a>
);
