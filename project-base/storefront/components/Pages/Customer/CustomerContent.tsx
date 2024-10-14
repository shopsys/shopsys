import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const CustomerContent: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const { url } = useDomainConfig();
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const [customerOrdersUrl, customerEditProfileUrl, customerComplaintsUrl, customerUsersUrl] =
        getInternationalizedStaticUrls(
            ['/customer/orders', '/customer/edit-profile', '/customer/complaints', '/customer/users'],
            url,
        );

    return (
        <>
            <Webline>
                <div className="text-center">
                    <h1>{t('Customer')}</h1>
                </div>
            </Webline>

            <Webline>
                <ul className="mb-8 flex flex-col flex-wrap gap-4 md:flex-row">
                    <CustomerListItem>
                        <ExtendedNextLink href={customerOrdersUrl} type="orderList">
                            <SearchListIcon className="mr-5 size-6" />
                            {t('My orders')}
                        </ExtendedNextLink>
                    </CustomerListItem>

                    <CustomerListItem>
                        <ExtendedNextLink href={customerComplaintsUrl} type="complaintList">
                            <ComplaintsIcon className="mr-5 size-6" />
                            {t('My complaints')}
                        </ExtendedNextLink>
                    </CustomerListItem>

                    <CustomerListItem>
                        <ExtendedNextLink href={customerEditProfileUrl} type="editProfile">
                            <EditIcon className="mr-5 size-6" />
                            {t('Edit profile')}
                        </ExtendedNextLink>
                    </CustomerListItem>

                    {canManageUsers && (
                        <CustomerListItem>
                            <ExtendedNextLink href={customerUsersUrl}>
                                <UserIcon className="mr-5 size-6" />
                                {t('Customer users')}
                            </ExtendedNextLink>
                        </CustomerListItem>
                    )}

                    <CustomerListItem>
                        <a tid={TIDs.customer_page_logout} onClick={logout}>
                            <ExitIcon className="mr-5 size-6" />
                            {t('Logout')}
                        </a>
                    </CustomerListItem>
                </ul>
            </Webline>
        </>
    );
};

const CustomerListItem: FC = ({ children }) => (
    <li
        className={twJoin(
            'block flex-1 cursor-pointer rounded-xl text-lg transition [&_a]:block [&_a]:h-full [&_a]:w-full [&_a]:p-5 [&_a]:text-text [&_a]:no-underline hover:[&_a]:no-underline',
            'border border-background bg-backgroundMore hover:border-borderAccentLess hover:bg-background',
        )}
    >
        {children}
    </li>
);
