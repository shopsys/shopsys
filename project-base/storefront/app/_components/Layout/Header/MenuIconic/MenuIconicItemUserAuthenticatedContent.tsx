'use client';

import { MenuIconicItemUserAuthenticatedContentListItem, MenuIconicSubItemLink } from './MenuIconicElements';
import { SalesRepresentative } from './SalesRepresentative';
import { useLogout } from 'app/_hooks/useLogout';
import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { CurrentCustomerType } from 'types/customer';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

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
    const { url } = useDomainConfig();
    const [
        customerOrdersUrl,
        customerComplaintsUrl,
        customerEditProfileUrl,
        customerChangePasswordUrl,
        // customerUsersUrl,
    ] = getInternationalizedStaticUrls(
        [
            '/customer/orders',
            '/customer/complaints',
            '/customer/edit-profile',
            '/customer/change-password',
            // '/customer/users',
        ],
        url,
    );

    const user = currentCustomerUser;

    return (
        <>
            <div className="mb-2 flex flex-col gap-1 rounded-xl bg-backgroundAccentLess px-3 py-4">
                <h5>
                    {user.firstName} {user.lastName}
                </h5>
                {user.companyName && <h6 className="text-textSubtle">{user.companyName}</h6>}
                <span
                    className={twJoin(
                        'max-w-64 overflow-x-auto whitespace-nowrap text-sm font-semibold',
                        '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-backgroundMost [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                    )}
                >
                    {user.email}
                </span>
            </div>
            <ul className="flex max-h-[87dvh] flex-col gap-2">
                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink href={customerOrdersUrl} tid={TIDs.header_my_orders_link} type="orderList">
                        <SearchListIcon className="size-6" />
                        {t('My orders')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink
                        href={customerComplaintsUrl}
                        tid={TIDs.header_my_complaints_link}
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
                        tid={TIDs.header_edit_profile_link}
                        type="editProfile"
                    >
                        <EditIcon className="size-6" />
                        {t('Edit profile')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink
                        href={customerChangePasswordUrl}
                        tid={TIDs.header_change_password_link}
                        type="changePassword"
                    >
                        <LockCheckIcon className="size-6" />
                        {t('Change password')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                        <ExitIcon className="size-6" />
                        {t('Logout')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>

                <SalesRepresentative salesRepresentative={user.salesRepresentative} />
            </ul>
        </>
    );
};
