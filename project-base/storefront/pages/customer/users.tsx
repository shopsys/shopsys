import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { CustomerUsersTable } from 'components/Pages/Customer/Users/CustomerUsersTable';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { CustomerUserAreaEnum } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useRedirectOnPermissionsChange } from 'utils/user/useRedirectOnPermissionsChange';

const UsersPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerUsersUrl] = getInternationalizedStaticUrls(['/customer/users'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Customer users'), slug: customerUsersUrl },
    ];
    const { canManageUsers } = useAuthorization();
    const { redirect } = useRedirectOnPermissionsChange();
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    if (canManageUsers === false) {
        redirect();
    }

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout breadcrumbs={breadcrumbs} title={t('Customer users')}>
                <PageHero
                    icon={UserIcon}
                    title={t('Customer users')}
                    description={t(
                        'Add, edit, and manage company users and permissions for seamless team collaboration and control.',
                    )}
                />

                <div className="flex w-full flex-col items-center gap-4">
                    <CustomerUsersTable />
                </div>
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                currentCustomerUserPrefetchMode: 'full',
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiManageCustomers],
                    authorizedAreas: [CustomerUserAreaEnum.B2B],
                },
                redisClient,
                domainConfig,
                t,
            }),
);

export default UsersPage;
