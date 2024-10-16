import { MenuIconicItemUserAuthenticatedContentListItem, MenuIconicSubItemLink } from './MenuIconicElements';
import { SalesRepresentative } from './SalesRepresentative';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const MenuIconicItemUserAuthenticatedContent: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const { url } = useDomainConfig();
    const [customerOrdersUrl, customerComplaintsUrl, customerEditProfileUrl, customerUsersUrl] =
        getInternationalizedStaticUrls(
            ['/customer/orders', '/customer/complaints', '/customer/edit-profile', '/customer/users'],
            url,
        );

    return (
        <ul className="flex max-h-[87dvh] flex-col gap-2 overflow-auto p-1">
            <MenuIconicItemUserAuthenticatedContentListItem>
                <MenuIconicSubItemLink href={customerOrdersUrl} tid={TIDs.header_my_orders_link} type="orderList">
                    <SearchListIcon className="mr-5 size-6" />
                    {t('My orders')}
                </MenuIconicSubItemLink>
            </MenuIconicItemUserAuthenticatedContentListItem>
            <MenuIconicItemUserAuthenticatedContentListItem>
                <MenuIconicSubItemLink
                    href={customerComplaintsUrl}
                    tid={TIDs.header_my_complaints_link}
                    type="complaintList"
                >
                    <SearchListIcon className="mr-5 size-6" />
                    {t('My complaints')}
                </MenuIconicSubItemLink>
            </MenuIconicItemUserAuthenticatedContentListItem>
            <MenuIconicItemUserAuthenticatedContentListItem>
                <MenuIconicSubItemLink
                    href={customerEditProfileUrl}
                    tid={TIDs.header_edit_profile_link}
                    type="editProfile"
                >
                    <EditIcon className="mr-5 size-6" />
                    {t('Edit profile')}
                </MenuIconicSubItemLink>
            </MenuIconicItemUserAuthenticatedContentListItem>
            {canManageUsers && (
                <MenuIconicItemUserAuthenticatedContentListItem>
                    <MenuIconicSubItemLink href={customerUsersUrl} type="customer-users">
                        <UserIcon className="mr-5 max-h-5.5 w-6" />
                        {t('Customer users')}
                    </MenuIconicSubItemLink>
                </MenuIconicItemUserAuthenticatedContentListItem>
            )}
            <MenuIconicItemUserAuthenticatedContentListItem>
                <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                    <ExitIcon className="mr-5 size-6" />
                    {t('Logout')}
                </MenuIconicSubItemLink>
            </MenuIconicItemUserAuthenticatedContentListItem>
            <SalesRepresentative />
        </ul>
    );
};
