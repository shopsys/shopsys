import { FC, useEffect, useState } from 'react';
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
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import Heading from 'components/Basic/Heading';
import Login from 'components/Blocks/Popup/Login';
import NextLink from 'next/link';
import nookies from 'nookies';
import Popup from 'components/Layout/Popup';
import { useAuth } from 'hooks/auth/UseAuth';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const MenuIconic: FC = () => {
    const testIdentifier = 'layout-header-menuiconic';

    const t = useTypedTranslationFunction();
    const [, [, logout]] = useAuth();
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [storesUrl, myOrdersUrl] = getInternationalizedStaticUrls(['/stores', '/customer/orders'], domainConfig.url);
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
            <MenuIconicListStyled data-testid={testIdentifier}>
                <MenuIconicItemStyled data-testid={testIdentifier + '-0'}>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled iconType="icon" icon="Chat" />
                            {t('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled data-testid={testIdentifier + '-1'}>
                    <NextLink href={storesUrl} passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled iconType="icon" icon="Marker" />
                            {t('Stores')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled data-testid={testIdentifier + '-2'}>
                    {isUserLoggedIn === true ? (
                        <MenuIconicItemLinkStyled hasSubmenu={true}>
                            <MenuIconicItemIconStyled iconType="icon" icon="User" />
                            {t('My account')}
                            <MenuIconicSubStyled>
                                <MenuIconicSubItemStyled data-testid={testIdentifier + '-sub-0'}>
                                    <NextLink href={myOrdersUrl} passHref>
                                        <MenuIconicSubItemLinkStyled>{t('My orders')}</MenuIconicSubItemLinkStyled>
                                    </NextLink>
                                </MenuIconicSubItemStyled>
                                <MenuIconicSubItemStyled data-testid={testIdentifier + '-sub-1'}>
                                    <MenuIconicSubItemLinkStyled>{t('Edit profile')}</MenuIconicSubItemLinkStyled>
                                </MenuIconicSubItemStyled>
                                <MenuIconicSubItemStyled data-testid={testIdentifier + '-sub-2'}>
                                    <MenuIconicSubItemLinkStyled onClick={logoutHandler}>
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
                {isUserLoggedIn === true ? (
                    <MenuIconicButtonMobileLinkStyled>
                        <MenuIconicItemIconStyled iconType="icon" icon="RemoveBold" onClick={logoutHandler} />
                    </MenuIconicButtonMobileLinkStyled>
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

/* @component */
export default MenuIconic;
