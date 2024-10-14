import { MenuIconicSubItemLink } from './MenuIconicElements';
import { SalesRepresentative } from './SalesRepresentative';
import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const MenuMyAccountList: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const user = useCurrentCustomerData();
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const { url } = useDomainConfig();
    const [customerOrdersUrl, customerComplaintsUrl, customerEditProfileUrl, customerUsersUrl] =
        getInternationalizedStaticUrls(
            ['/customer/orders', '/customer/complaints', '/customer/edit-profile', '/customer/users'],
            url,
        );

    return (
        <>
            <div className="mb-2 flex flex-col gap-1 rounded-xl bg-backgroundAccentLess px-3 py-4">
                <h5>
                    {user?.firstName} {user?.lastName}
                </h5>
                {user?.companyName && <h6 className="text-textSubtle">{user.companyName}</h6>}
                <span
                    className={twJoin(
                        'max-w-64 overflow-x-auto whitespace-nowrap text-sm font-semibold',
                        '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-backgroundMost [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                    )}
                >
                    {user?.email}
                </span>
            </div>
            <ul className="flex max-h-[87dvh] flex-col gap-2">
                <MenuMyAccountListItem>
                    <MenuIconicSubItemLink href={customerOrdersUrl} tid={TIDs.header_my_orders_link} type="orderList">
                        <SearchListIcon className="size-6" />
                        {t('My orders')}
                    </MenuIconicSubItemLink>
                </MenuMyAccountListItem>
                <MenuMyAccountListItem>
                    <MenuIconicSubItemLink
                        href={customerComplaintsUrl}
                        tid={TIDs.header_my_complaints_link}
                        type="complaintList"
                    >
                        <ComplaintsIcon className="size-6" />
                        {t('My complaints')}
                    </MenuIconicSubItemLink>
                </MenuMyAccountListItem>
                <MenuMyAccountListItem>
                    <MenuIconicSubItemLink
                        href={customerEditProfileUrl}
                        tid={TIDs.header_edit_profile_link}
                        type="editProfile"
                    >
                        <EditIcon className="size-6" />
                        {t('Edit profile')}
                    </MenuIconicSubItemLink>
                </MenuMyAccountListItem>
                {canManageUsers && (
                    <MenuMyAccountListItem>
                        <MenuIconicSubItemLink href={customerUsersUrl} type="customer-users">
                            <UserIcon className="max-h-5.5 w-6" />
                            {t('Customer users')}
                        </MenuIconicSubItemLink>
                    </MenuMyAccountListItem>
                )}
                <MenuMyAccountListItem>
                    <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                        <ExitIcon className="size-6" />
                        {t('Logout')}
                    </MenuIconicSubItemLink>
                </MenuMyAccountListItem>
                <SalesRepresentative />
            </ul>
        </>
    );
};

const MenuMyAccountListItem: FC = ({ children }) => (
    <li
        className={twJoin(
            'h-14 rounded-xl border border-background bg-backgroundMore hover:border-borderAccentLess hover:bg-background',
        )}
    >
        {children}
    </li>
);
