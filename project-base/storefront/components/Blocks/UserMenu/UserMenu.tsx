import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import {
    MenuIconicItemUserAuthenticatedContentListItem,
    MenuIconicSubItemLink,
} from 'components/Layout/Header/MenuIconic/MenuIconicElements';
import { SalesRepresentative } from 'components/Layout/Header/MenuIconic/SalesRepresentative';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { usePathname } from 'next/navigation';
import { memo } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { useUserProfileSectionLabel } from 'utils/user/useUserProfileSectionLabel';

type UserMenuProps = {
    className?: string;
};

const UserMenuComp: FC<UserMenuProps> = ({ className }) => {
    const { t } = useTranslation();
    const pathname = usePathname();
    const logout = useLogout();
    const user = useCurrentCustomerData();
    const setIsUserMenuOpen = useSessionStore((s) => s.setIsUserMenuOpen);

    const { canManageUsers, canCreateOrder, canViewCompanyOrders, canCreateComplaint, canViewCompanyComplaints } =
        useAuthorization();
    const { url } = useDomainConfig();
    const [
        customerOrdersUrl,
        customerComplaintsUrl,
        customerEditProfileUrl,
        wishlistUrl,
        customerChangePasswordUrl,
        customerUsersUrl,
    ] = getInternationalizedStaticUrls(
        [
            '/customer/orders',
            '/customer/complaints',
            '/customer/edit-profile',
            '/wishlist',
            '/customer/change-password',
            '/customer/users',
        ],
        url,
    );
    const userProfileSectionLabel = useUserProfileSectionLabel();

    return (
        <div className={twMergeCustom('flex flex-col gap-3', className)}>
            <div className="bg-background-accent-less flex flex-col gap-1 rounded-xl px-3 py-4">
                <span className="h5">
                    {user?.firstName} {user?.lastName}
                </span>
                {user?.companyName && <span className="h6 text-text-less">{user.companyName}</span>}
                <span
                    className={twJoin(
                        'max-w-64 overflow-x-auto text-sm font-semibold whitespace-nowrap',
                        '[&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar]:h-1 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent',
                    )}
                >
                    {user?.email}
                </span>
            </div>

            <ul className="flex max-h-[87dvh] flex-col gap-2">
                {(canCreateOrder || canViewCompanyOrders) && (
                    <MenuIconicItemUserAuthenticatedContentListItem isActive={pathname === customerOrdersUrl}>
                        <MenuIconicSubItemLink
                            href={customerOrdersUrl}
                            isActive={pathname === customerOrdersUrl}
                            tid={TIDs.user_menu_my_orders_link}
                            type="orderList"
                            onClick={() => setIsUserMenuOpen(false)}
                        >
                            <SearchListIcon className="size-6" />
                            {t('My orders')}
                        </MenuIconicSubItemLink>
                    </MenuIconicItemUserAuthenticatedContentListItem>
                )}

                {(canCreateComplaint || canViewCompanyComplaints) && (
                    <MenuIconicItemUserAuthenticatedContentListItem isActive={pathname === customerComplaintsUrl}>
                        <MenuIconicSubItemLink
                            href={customerComplaintsUrl}
                            isActive={pathname === customerComplaintsUrl}
                            tid={TIDs.user_menu_my_complaints_link}
                            type="complaintList"
                            onClick={() => setIsUserMenuOpen(false)}
                        >
                            <ComplaintsIcon className="size-6" />
                            {t('My complaints')}
                        </MenuIconicSubItemLink>
                    </MenuIconicItemUserAuthenticatedContentListItem>
                )}

                {canManageUsers && (
                    <MenuIconicItemUserAuthenticatedContentListItem isActive={pathname === customerUsersUrl}>
                        <MenuIconicSubItemLink
                            href={customerUsersUrl}
                            isActive={pathname === customerUsersUrl}
                            type="customer-users"
                            onClick={() => setIsUserMenuOpen(false)}
                        >
                            <UserIcon className="size-6" />
                            {t('Customer users')}
                        </MenuIconicSubItemLink>
                    </MenuIconicItemUserAuthenticatedContentListItem>
                )}

                <MenuIconicItemUserAuthenticatedContentListItem isActive={pathname === customerEditProfileUrl}>
                    <MenuIconicSubItemLink
                        href={customerEditProfileUrl}
                        isActive={pathname === customerEditProfileUrl}
                        tid={TIDs.user_menu_edit_profile_link}
                        type="editProfile"
                        onClick={() => setIsUserMenuOpen(false)}
                    >
                        <EditIcon className="size-6" />
                        {userProfileSectionLabel}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem isActive={pathname === wishlistUrl}>
                    <MenuIconicSubItemLink
                        href={wishlistUrl}
                        isActive={pathname === wishlistUrl}
                        type="wishlist"
                        onClick={() => setIsUserMenuOpen(false)}
                    >
                        <HeartIcon className="size-6" />
                        {t('Wishlist')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem isActive={pathname === customerChangePasswordUrl}>
                    <MenuIconicSubItemLink
                        href={customerChangePasswordUrl}
                        isActive={pathname === customerChangePasswordUrl}
                        tid={TIDs.user_menu_change_password_link}
                        type="changePassword"
                        onClick={() => setIsUserMenuOpen(false)}
                    >
                        <LockCheckIcon className="size-6" />
                        {t('Change password')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink tid={TIDs.user_menu_logout} title={t('Logout')} onClick={logout}>
                        <ExitIcon className="size-6" />
                        {t('Logout')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>
            </ul>

            <SalesRepresentative />
        </div>
    );
};

export const UserMenu = memo(UserMenuComp);
