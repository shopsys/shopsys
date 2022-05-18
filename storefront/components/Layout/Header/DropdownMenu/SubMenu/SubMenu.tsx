import { SubMenuItemStyled, SubMenuStyled } from './SubMenu.style';
import { useAuth } from 'hooks/auth/UseAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import Link from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

const SubMenu: FC = () => {
    const testIdentifier = 'layout-header-dropdownmenu-submenu';
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const { isUserLoggedIn } = useCurrentUserData();
    const [storesUrl, loginUrl] = getInternationalizedStaticUrls(['/stores', '/login'], domainConfig.url);
    const [, [, logout]] = useAuth();

    const logoutHandler = () => {
        logout();
    };

    return (
        <SubMenuStyled data-testid={testIdentifier}>
            <Link href="/" passHref>
                <SubMenuItemStyled data-testid={testIdentifier + '-0'}>{t('Customer service')}</SubMenuItemStyled>
            </Link>
            <Link href={storesUrl} passHref>
                <SubMenuItemStyled data-testid={testIdentifier + '-1'}>{t('Stores')}</SubMenuItemStyled>
            </Link>

            {isUserLoggedIn ? (
                <SubMenuItemStyled onClick={logoutHandler}>{t('Logout')}</SubMenuItemStyled>
            ) : (
                <Link href={loginUrl} passHref>
                    <SubMenuItemStyled data-testid={testIdentifier + '-2'}>{t('Sign in')}</SubMenuItemStyled>
                </Link>
            )}
        </SubMenuStyled>
    );
};

/* @component */
export default SubMenu;
