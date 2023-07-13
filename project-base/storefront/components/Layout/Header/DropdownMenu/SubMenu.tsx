import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-submenu';

export const SubMenu: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const { isUserLoggedIn } = useCurrentUserData();
    const [storesUrl, loginUrl] = getInternationalizedStaticUrls(['/stores', '/login'], url);
    const { logout } = useAuth();

    return (
        <div className="mt-5 flex flex-col" data-testid={TEST_IDENTIFIER}>
            <SubMenuItem href="/" dataTestId={TEST_IDENTIFIER + '-0'}>
                {t('Customer service')}
            </SubMenuItem>
            <SubMenuItem href={storesUrl} dataTestId={TEST_IDENTIFIER + '-1'}>
                {t('Stores')}
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
        <ExtendedNextLink
            href={href}
            passHref
            type="static"
            className="mb-5 px-8 text-sm text-dark no-underline"
        ></ExtendedNextLink>;
    }

    return (
        <a className="mb-5 px-8 text-sm text-dark no-underline" onClick={onClick} data-testid={dataTestId}>
            {children}
        </a>
    );
};
