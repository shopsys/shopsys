import {
    MenuIconicButtonMobileLinkStyled,
    MenuIconicButtonMobileStyled,
    MenuIconicItemIconStyled,
    MenuIconicItemLinkStyled,
    MenuIconicItemStyled,
    MenuIconicListStyled,
    MenuIconicSubItemLinkStyled,
    MenuIconicSubItemStyled,
    MenuIconicSubStyled,
} from './MenuIconic.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { Popup } from 'components/Layout/Popup/Popup';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import NextLink from 'next/link';
import nookies from 'nookies';
import { FC, useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

export const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();
    const [, [, logout]] = useAuth();
    const { isUserLoggedIn } = useCurrentUserData();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [storesUrl, customerUrl, customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/stores', '/customer', '/customer/orders', '/customer/edit-profile'],
        domainConfig.url,
    );
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);

    const loginHandler = () => {
        setIsLoginPopupOpened(true);
    };

    const logoutHandler = () => {
        logout();
        nookies.destroy(null, 'contactInformation');
    };

    useEffect(() => {
        if (isUserLoggedIn === true) {
            setIsLoginPopupOpened(false);
        }
    }, [isUserLoggedIn]);

    const onCloseLoginPopupHandler = (): void => {
        setIsLoginPopupOpened(false);
    };

    return (
        <>
            <MenuIconicListStyled data-testid={TEST_IDENTIFIER}>
                <MenuIconicItemStyled data-testid={TEST_IDENTIFIER + '-0'}>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled iconType="icon" icon="Chat" />
                            {t('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled data-testid={TEST_IDENTIFIER + '-1'}>
                    <NextLink href={storesUrl} passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled iconType="icon" icon="Marker" />
                            {t('Stores')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled data-testid={TEST_IDENTIFIER + '-2'}>
                    {isUserLoggedIn ? (
                        <MenuIconicItemLinkStyled hasSubmenu>
                            <MenuIconicItemIconStyled iconType="icon" icon="User" />
                            {t('My account')}
                            <MenuIconicSubStyled>
                                <MenuIconicSubItemStyled data-testid={TEST_IDENTIFIER + '-sub-0'}>
                                    <NextLink href={customerOrdersUrl} passHref>
                                        <MenuIconicSubItemLinkStyled>{t('My orders')}</MenuIconicSubItemLinkStyled>
                                    </NextLink>
                                </MenuIconicSubItemStyled>
                                <MenuIconicSubItemStyled>
                                    <NextLink
                                        href={customerEditProfileUrl}
                                        passHref
                                        data-testid={TEST_IDENTIFIER + '-sub-1'}
                                    >
                                        <MenuIconicSubItemLinkStyled>{t('Edit profile')}</MenuIconicSubItemLinkStyled>
                                    </NextLink>
                                </MenuIconicSubItemStyled>
                                <MenuIconicSubItemStyled>
                                    <MenuIconicSubItemLinkStyled
                                        onClick={logoutHandler}
                                        data-testid={TEST_IDENTIFIER + '-sub-2'}
                                    >
                                        {t('Logout')}
                                    </MenuIconicSubItemLinkStyled>
                                </MenuIconicSubItemStyled>
                            </MenuIconicSubStyled>
                        </MenuIconicItemLinkStyled>
                    ) : (
                        <MenuIconicItemLinkStyled onClick={loginHandler}>
                            <MenuIconicItemIconStyled iconType="icon" icon="User" />
                            {t('Login')}
                        </MenuIconicItemLinkStyled>
                    )}
                </MenuIconicItemStyled>
            </MenuIconicListStyled>
            <MenuIconicButtonMobileStyled>
                {isUserLoggedIn ? (
                    <NextLink href={customerUrl} passHref>
                        <MenuIconicButtonMobileLinkStyled>
                            <MenuIconicItemIconStyled iconType="icon" icon="User" />
                        </MenuIconicButtonMobileLinkStyled>
                    </NextLink>
                ) : (
                    <MenuIconicButtonMobileLinkStyled onClick={loginHandler}>
                        <MenuIconicItemIconStyled iconType="icon" icon="User" />
                    </MenuIconicButtonMobileLinkStyled>
                )}
            </MenuIconicButtonMobileStyled>
            <Popup isVisible={isLoginPopupOpened} onCloseCallback={onCloseLoginPopupHandler}>
                <Heading type="h2">{t('Login')}</Heading>
                <Login />
            </Popup>
        </>
    );
};
