import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { MenuIconicItemLink, MenuIconicItemIcon, MenuIconicSubItemLink } from './MenuIconicElements';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useAuth } from 'hooks/auth/useAuth';
import { Heading } from 'components/Basic/Heading/Heading';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { useState } from 'react';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import dynamic from 'next/dynamic';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

const TEST_IDENTIFIER = 'layout-header-menuiconic-login';

export const MenuIconicItemLogin: FC = () => {
    const t = useTypedTranslationFunction();
    const { logout } = useAuth();
    const { url } = useDomainConfig();
    const { isUserLoggedIn } = useCurrentUserData();
    const [customerUrl, customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/orders', '/customer/edit-profile'],
        url,
    );
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);

    const handleLogin = () => setIsLoginPopupOpened(true);

    const MenuItem = isUserLoggedIn ? (
        <div className="group">
            <MenuIconicItemLink
                href={customerUrl}
                className="rounded-t-xl p-3 group-hover:bg-white group-hover:text-dark max-vl:hidden"
                dataTestId={TEST_IDENTIFIER + '-my-account'}
            >
                <MenuIconicItemIcon icon="User" className="group-hover:text-dark" />
                {t('My account')}
            </MenuIconicItemLink>
            <ul className="pointer-events-none absolute top-full right-0 z-cart block min-w-[150px] origin-top-right scale-50 rounded-xl rounded-tr-none bg-white opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100">
                <li className="block" data-testid={TEST_IDENTIFIER + '-sub-0'}>
                    <MenuIconicSubItemLink href={customerOrdersUrl}>{t('My orders')}</MenuIconicSubItemLink>
                </li>
                <li className="block border-t border-border">
                    <MenuIconicSubItemLink href={customerEditProfileUrl} dataTestId={TEST_IDENTIFIER + '-sub-1'}>
                        {t('Edit profile')}
                    </MenuIconicSubItemLink>
                </li>
                <li className="block border-t border-border">
                    <MenuIconicSubItemLink onClick={logout} dataTestId={TEST_IDENTIFIER + '-sub-2'}>
                        {t('Logout')}
                    </MenuIconicSubItemLink>
                </li>
            </ul>
        </div>
    ) : (
        <MenuIconicItemLink
            onClick={handleLogin}
            className="cursor-pointer max-vl:hidden"
            dataTestId={TEST_IDENTIFIER + '-link-popup'}
        >
            <MenuIconicItemIcon icon="User" />
            {t('Login')}
        </MenuIconicItemLink>
    );

    return (
        <>
            {MenuItem}

            {/* MOBILE MENU */}
            <div className="order-2 ml-1 flex h-9 w-9 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                {isUserLoggedIn ? (
                    <ExtendedNextLink href={customerUrl} type="static">
                        <div className="relative flex h-full w-full items-center justify-center text-white transition-colors">
                            <MenuIconicItemIcon icon="User" className="mr-0" />
                        </div>
                    </ExtendedNextLink>
                ) : (
                    <div
                        className="relative flex h-full w-full items-center justify-center text-white transition-colors"
                        onClick={handleLogin}
                    >
                        <MenuIconicItemIcon icon="User" className="mr-0" />
                    </div>
                )}
            </div>
            {!isUserLoggedIn && isLoginPopupOpened && (
                <Popup onCloseCallback={() => setIsLoginPopupOpened(false)}>
                    <Heading type="h2">{t('Login')}</Heading>
                    <Login />
                </Popup>
            )}
        </>
    );
};
