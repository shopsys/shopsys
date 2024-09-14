import { MenuIconicItemLink, MenuIconicSubItemLink } from './MenuIconicElements';
import { SalesRepresentative } from './SalesRepresentative';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { useLogout } from 'utils/auth/useLogout';
import { desktopFirstSizes } from 'utils/mediaQueries';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useDebounce } from 'utils/useDebounce';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const { url } = useDomainConfig();
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const [customerUrl, customerOrdersUrl, customerComplaintsUrl, customerEditProfileUrl, customerUsersUrl] =
        getInternationalizedStaticUrls(
            ['/customer', '/customer/orders', '/customer/complaints', '/customer/edit-profile', '/customer/users'],
            url,
        );
    const [isClicked, setIsClicked] = useState(false);
    const [isHovered, setIsHovered] = useState(false);
    const isHoveredDelayed = useDebounce(isHovered, 200);

    const userMenuItemTwClass =
        'h-14 rounded-xl bg-backgroundAccentLess border border-background hover:bg-background hover:border-borderAccentLess';
    const userMenuItemIconTwClass = 'flex flex-row w-[44px] justify-start';

    const { width } = useGetWindowSize();
    const isDesktop = width > desktopFirstSizes.tablet;

    return (
        <>
            <div
                tid={TIDs.my_account_link}
                className={twJoin(
                    'group w-10 sm:w-12 lg:relative lg:-mb-2.5 lg:w-[72px] lg:pb-2.5',
                    (isClicked || isHovered) && 'z-aboveOverlay',
                )}
                onMouseEnter={() => isDesktop && setIsHovered(true)}
                onMouseLeave={() => isDesktop && setIsHovered(false)}
            >
                <MenuIconicItemLink
                    className="text-nowrap rounded-t transition-all max-lg:hidden"
                    href={customerUrl}
                    type="account"
                >
                    <div className="relative">
                        <UserIcon className="size-6" />
                        <div className="absolute -right-1 -top-1 h-[10px] w-[10px] rounded-full bg-actionPrimaryBackground" />
                    </div>
                    {t('My account')}
                </MenuIconicItemLink>

                <div className="order-2 flex cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                    <div
                        className="relative flex items-center justify-center text-textInverted transition-colors"
                        onClick={() => {
                            setIsClicked(!isClicked);
                            setIsClicked(!isHovered);
                        }}
                    >
                        <UserIcon className="size-6 text-textInverted" />
                        <div className="absolute -right-1 -top-1 h-[10px] w-[10px] rounded-full bg-actionPrimaryBackground" />
                    </div>
                </div>

                <div
                    className={twMergeCustom(
                        'pointer-events-none absolute right-0 top-0 block min-w-[355px] origin-top-right rounded-xl px-5 lg:-right-[100%]',
                        'lg:top-full lg:p-5 lg:transition-all',
                        'scale-50 bg-none opacity-0',
                        isHoveredDelayed &&
                            'z-cart group-hover:pointer-events-auto group-hover:scale-100 group-hover:bg-background group-hover:opacity-100',
                        isClicked &&
                            'pointer-events-auto fixed right-0 top-0 z-aboveOverlay h-dvh scale-100 rounded-none bg-background opacity-100 transition-[right]',
                    )}
                >
                    <div className="m-5 flex flex-row justify-between lg:hidden">
                        <span className="w-full text-center text-base">{t('My account')}</span>
                        <RemoveIcon
                            className="w-4 cursor-pointer text-borderAccent"
                            onClick={() => setIsClicked(false)}
                        />
                    </div>
                    <ul className="flex max-h-[87dvh] flex-col gap-2 overflow-auto p-1">
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink
                                href={customerOrdersUrl}
                                tid={TIDs.header_my_orders_link}
                                type="orderList"
                            >
                                <div className={userMenuItemIconTwClass}>
                                    <SearchListIcon className="size-6" />
                                </div>
                                {t('My orders')}
                            </MenuIconicSubItemLink>
                        </li>
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink
                                href={customerComplaintsUrl}
                                tid={TIDs.header_my_complaints_link}
                                type="complaintList"
                            >
                                <div className={userMenuItemIconTwClass}>
                                    <SearchListIcon className="size-6" />
                                </div>
                                {t('My complaints')}
                            </MenuIconicSubItemLink>
                        </li>
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink
                                href={customerEditProfileUrl}
                                tid={TIDs.header_edit_profile_link}
                                type="editProfile"
                            >
                                <div className={userMenuItemIconTwClass}>
                                    <EditIcon className="size-6" />
                                </div>
                                {t('Edit profile')}
                            </MenuIconicSubItemLink>
                        </li>
                        {canManageUsers && (
                            <li className={userMenuItemTwClass}>
                                <MenuIconicSubItemLink href={customerUsersUrl} type="customer-users">
                                    <div className={userMenuItemIconTwClass}>
                                        <UserIcon className="max-h-[22px] w-6" />
                                    </div>
                                    {t('Customer users')}
                                </MenuIconicSubItemLink>
                            </li>
                        )}
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                                <div className={userMenuItemIconTwClass}>
                                    <ExitIcon className="size-6" />
                                </div>
                                {t('Logout')}
                            </MenuIconicSubItemLink>
                        </li>
                        <SalesRepresentative />
                    </ul>
                </div>
            </div>

            <Overlay
                isActive={isClicked || isHoveredDelayed}
                onClick={() => {
                    setIsClicked(false);
                    setIsHovered(false);
                }}
            />
        </>
    );
};
