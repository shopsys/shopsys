'use client';

import { MenuIconicItemUserAuthenticatedContentListItem, MenuIconicSubItemLink } from './MenuIconicElements';
import { SalesRepresentative } from './SalesRepresentative';
import { useInternationalizedStaticUrls } from 'app/_hooks/useInternationalizedStaticUrls';
import { useLogout } from 'app/_hooks/useLogout';
import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TIDs } from 'cypress/tids';
import { twJoin } from 'tailwind-merge';
import { CurrentCustomerType } from 'types/customer';

type MenuIconicItemUserAuthenticatedContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const MenuIconicItemUserAuthenticatedContent: FC<MenuIconicItemUserAuthenticatedContentProps> = ({
    currentCustomerUser,
}) => {
    const { t } = useTranslation();
    const logout = useLogout();

    // TODO permisions
    // const { canManageUsers } = useUserPermissions();
    const [
        customerOrdersUrl,
        customerComplaintsUrl,
        customerEditProfileUrl,
        customerChangePasswordUrl,
        // customerUsersUrl,
    ] = useInternationalizedStaticUrls([
        '/customer/orders',
        '/customer/complaints',
        '/customer/edit-profile',
        '/customer/change-password',
        // '/customer/users',
    ]);

    const user = currentCustomerUser;

    return (
        <>
            <div className="bg-backgroundAccentLess mb-2 flex flex-col gap-1 rounded-xl px-3 py-4">
                <h5>
                    {user.firstName} {user.lastName}
                </h5>
                {user.companyName && <h6 className="text-text-less">{user.companyName}</h6>}
                <span
                    className={twJoin(
                        'max-w-64 overflow-x-auto text-sm font-semibold whitespace-nowrap',
                        '[&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar]:h-1 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent',
                    )}
                >
                    {user.email}
                </span>
            </div>
            <ul className="flex max-h-[87dvh] flex-col gap-2">
                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink
                        href={customerOrdersUrl}
                        tid={TIDs.user_menu_my_orders_link}
                        type="orderList"
                    >
                        <SearchListIcon className="size-6" />
                        {t('My orders')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink
                        href={customerComplaintsUrl}
                        tid={TIDs.user_menu_my_orders_link}
                        type="complaintList"
                    >
                        <ComplaintsIcon className="size-6" />
                        {t('My complaints')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                {/* {canManageUsers && (
                    <MenuIconicItemUserAuthenticatedContentListItem>
                        <MenuIconicSubItemLink href={customerUsersUrl} type="customer-users">
                            <UserIcon className="max-h-5.5 w-6" />
                            {t('Customer users')}
                        </MenuIconicSubItemLink>
                    </MenuIconicItemUserAuthenticatedContentListItem>
                )} */}

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink
                        href={customerEditProfileUrl}
                        tid={TIDs.user_menu_edit_profile_link}
                        type="editProfile"
                    >
                        <EditIcon className="size-6" />
                        {t('Edit profile')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink
                        href={customerChangePasswordUrl}
                        tid={TIDs.user_menu_change_password_link}
                        type="changePassword"
                    >
                        <LockCheckIcon className="size-6" />
                        {t('Change password')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink tid={TIDs.user_menu_logout} onClick={logout}>
                        <ExitIcon className="size-6" />
                        {t('Logout')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <SalesRepresentative salesRepresentative={user.salesRepresentative} />
            </ul>
        </>
    );
};
