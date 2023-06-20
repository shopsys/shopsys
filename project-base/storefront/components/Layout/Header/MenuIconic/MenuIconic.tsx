import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { IconName } from 'components/Basic/Icon/IconsSvgMap';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { Popup } from 'components/Layout/Popup/Popup';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import NextLink from 'next/link';
import { useEffect, useState } from 'react';
import { twMergeCustom } from 'utils/twMerge';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

export const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();
    const { logout } = useAuth();
    const { isUserLoggedIn } = useCurrentUserData();
    const { url } = useDomainConfig();
    const [storesUrl, customerUrl, customerOrdersUrl, customerEditProfileUrl, productsComparisonUrl] =
        getInternationalizedStaticUrls(
            ['/stores', '/customer', '/customer/orders', '/customer/edit-profile', '/products-comparison'],
            url,
        );
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);
    const { getComparisonProducts } = useHandleCompare('');

    const loginHandler = () => {
        setIsLoginPopupOpened(true);
    };

    const logoutHandler = () => {
        logout();
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
            <ul className="hidden lg:flex" data-testid={TEST_IDENTIFIER}>
                <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-0'}>
                    <MenuIconicItemLink href="/">
                        <MenuIconicItemIcon icon="Chat" />
                        {t('Customer service')}
                    </MenuIconicItemLink>
                </MenuIconicItem>
                <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-1'}>
                    <MenuIconicItemLink href={storesUrl}>
                        <MenuIconicItemIcon icon="Marker" />
                        {t('Stores')}
                    </MenuIconicItemLink>
                </MenuIconicItem>
                <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-2'}>
                    {isUserLoggedIn ? (
                        <MenuIconicItemLink className="group rounded-t-xl p-3 hover:bg-white hover:text-dark">
                            <MenuIconicItemIcon icon="User" className="group-hover:text-dark" />
                            {t('My account')}
                            <ul className="pointer-events-none absolute top-full right-0 z-cart block min-w-[150px] origin-top-right scale-50 rounded-xl rounded-tr-none bg-white opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100">
                                <li className="block" data-testid={TEST_IDENTIFIER + '-sub-0'}>
                                    <MenuIconicSubItemLink href={customerOrdersUrl}>
                                        {t('My orders')}
                                    </MenuIconicSubItemLink>
                                </li>
                                <li className="block border-t border-border">
                                    <MenuIconicSubItemLink
                                        href={customerEditProfileUrl}
                                        dataTestId={TEST_IDENTIFIER + '-sub-1'}
                                    >
                                        {t('Edit profile')}
                                    </MenuIconicSubItemLink>
                                </li>
                                <li className="block border-t border-border">
                                    <MenuIconicSubItemLink
                                        onClick={logoutHandler}
                                        dataTestId={TEST_IDENTIFIER + '-sub-2'}
                                    >
                                        {t('Logout')}
                                    </MenuIconicSubItemLink>
                                </li>
                            </ul>
                        </MenuIconicItemLink>
                    ) : (
                        <MenuIconicItemLink onClick={loginHandler}>
                            <MenuIconicItemIcon icon="User" />
                            {t('Login')}
                        </MenuIconicItemLink>
                    )}
                </MenuIconicItem>
                <MenuIconicItem data-testid={TEST_IDENTIFIER + '-3'}>
                    <NextLink href={productsComparisonUrl} passHref>
                        <MenuIconicItemLink>
                            <MenuIconicItemIcon icon="Compare" />
                            {t('Comparison')}
                            {getComparisonProducts().length > 0 && <span>({getComparisonProducts().length})</span>}
                        </MenuIconicItemLink>
                    </NextLink>
                </MenuIconicItem>
            </ul>
            <div className="order-2 ml-1 flex h-10 w-10 cursor-pointer items-center justify-center text-lg outline-none lg:hidden ">
                {isUserLoggedIn ? (
                    <NextLink href={customerUrl} passHref>
                        <div className="relative flex h-full w-full items-center justify-center text-white transition-colors">
                            <MenuIconicItemIcon icon="User" />
                        </div>
                    </NextLink>
                ) : (
                    <div
                        className="relative flex h-full w-full items-center justify-center text-white transition-colors"
                        onClick={loginHandler}
                    >
                        <MenuIconicItemIcon icon="User" />
                    </div>
                )}
            </div>
            <Popup isVisible={isLoginPopupOpened} onCloseCallback={onCloseLoginPopupHandler}>
                <Heading type="h2">{t('Login')}</Heading>
                <Login />
            </Popup>
        </>
    );
};

const MenuIconicItemIcon: FC<{ icon: IconName }> = ({ icon, className }) => (
    <Icon iconType="icon" icon={icon} className={twMergeCustom('mr-2 w-4 text-white', className)} />
);

const MenuIconicItem: FC = ({ children, dataTestId }) => (
    <li className="relative mr-5 flex last:mr-0 xl:mr-8" data-testid={dataTestId}>
        {children}
    </li>
);

const MenuIconicSubItemLink: FC<{ onClick?: () => void; href?: string }> = ({
    children,
    href,
    onClick,
    dataTestId,
}) => {
    const content = (
        <a className="block py-3 px-5 text-sm text-dark no-underline" data-testid={dataTestId} onClick={onClick}>
            {children}
        </a>
    );

    if (href !== undefined) {
        return (
            <NextLink href={href} passHref>
                {content}
            </NextLink>
        );
    }

    return content;
};

const MenuIconicItemLink: FC<{ onClick?: () => void; href?: string }> = ({ children, className, href, onClick }) => {
    const content = (
        <a
            className={twMergeCustom(
                'flex items-center justify-center rounded-tr-none text-sm text-white no-underline transition-colors hover:text-white hover:no-underline',
                className,
            )}
            onClick={onClick}
        >
            {children}
        </a>
    );

    if (href !== undefined) {
        return (
            <NextLink href={href} passHref>
                {content}
            </NextLink>
        );
    }

    return content;
};
