import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { Button } from 'components/Forms/Button/Button';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { CustomerUsersTable } from 'components/Pages/Customer/Users/CustomerUsersTable';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { CustomerUserAreaEnum, CustomerUserRoleEnum } from 'types/customer';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useRedirectOnPermissionsChange } from 'utils/user/useRedirectOnPermissionsChange';

const ManageCustomerUserPopup = dynamic(
    () =>
        import('components/Blocks/Popup/ManageCustomerUserPopup').then(
            (component) => component.ManageCustomerUserPopup,
        ),
    {
        ssr: false,
    },
);

const UsersPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const [customerUsersUrl] = getInternationalizedStaticUrls(['/customer/users'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Customer users'), slug: customerUsersUrl },
    ];
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const { redirect } = useRedirectOnPermissionsChange();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    if (canManageUsers === false) {
        redirect();
    }

    const openManageCustomerUserPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        customerUser?: TypeSimpleCustomerUserFragment,
    ) => {
        e.stopPropagation();
        updatePortalContent(
            <ManageCustomerUserPopup customerUser={customerUser} mode={customerUser ? 'edit' : 'add'} />,
        );
    };

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('Customer users')} title={t('Customer users')}>
                <div className="w-full flex flex-col gap-4">
                    <Button className="w-fit" size="small" onClick={(e) => openManageCustomerUserPopup(e)}>
                        {t('Add new user')}
                    </Button>
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
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedRoles: [CustomerUserRoleEnum.ROLE_API_ALL],
                    authorizedAreas: [CustomerUserAreaEnum.B2B],
                },
                redisClient,
                domainConfig,
                t,
            }),
);

export default UsersPage;
