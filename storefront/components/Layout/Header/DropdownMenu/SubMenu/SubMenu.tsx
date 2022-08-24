import { SubMenuItemStyled, SubMenuStyled } from './SubMenu.style';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/UseAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-submenu';

export const SubMenu: FC = () => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const { isUserLoggedIn } = useCurrentUserData();
    const [storesUrl, loginUrl] = getInternationalizedStaticUrls(['/stores', '/login'], domainConfig.url);
    const [, [, logout]] = useAuth();

    return (
        <SubMenuStyled data-testid={TEST_IDENTIFIER}>
            <NextLink href="/" passHref>
                <SubMenuItemStyled data-testid={TEST_IDENTIFIER + '-0'}>{t('Customer service')}</SubMenuItemStyled>
            </NextLink>
            <NextLink href={storesUrl} passHref>
                <SubMenuItemStyled data-testid={TEST_IDENTIFIER + '-1'}>{t('Stores')}</SubMenuItemStyled>
            </NextLink>

            {isUserLoggedIn ? (
                <SubMenuItemStyled onClick={logout}>{t('Logout')}</SubMenuItemStyled>
            ) : (
                <NextLink href={loginUrl} passHref>
                    <SubMenuItemStyled data-testid={TEST_IDENTIFIER + '-2'}>{t('Sign in')}</SubMenuItemStyled>
                </NextLink>
            )}
        </SubMenuStyled>
    );
};
