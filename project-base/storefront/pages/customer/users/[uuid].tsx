import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { EditCustomerUserProfileContent } from 'components/Pages/Customer/EditCustomerUserContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeCompanyCustomerUser } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useCurrentCustomerUsers } from 'utils/user/useCurrentCustomerUsers';

const EditUserPage: FC<{ uuid?: string }> = ({ uuid }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerUsersUrl, customerUserEditUrlById] = getInternationalizedStaticUrls(
        ['/customer/users', '/customer/users/:uuid'],
        url,
    );
    const user = useCurrentCustomerUsers().find((user) => user.uuid === uuid) as TypeCompanyCustomerUser;
    const currentCustomerUserData = useCurrentCustomerData(user);
    const fullName = getFullName(currentCustomerUserData?.firstName, currentCustomerUserData?.lastName);

    const breadcrumbs: TypeBreadcrumbFragment[] = [
        {
            __typename: 'Link',
            name: t('Customer users'),
            slug: customerUsersUrl,
        },
        {
            __typename: 'Link',
            name: t('Edit user {{ name }}', {
                name: fullName,
            }),
            slug: customerUserEditUrlById,
        },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout
                breadcrumbs={breadcrumbs}
                pageHeading={t('Edit user {{ name }}', {
                    name: fullName,
                })}
                title={t('Edit user {{ name }}', {
                    name: fullName,
                })}
            >
                {currentCustomerUserData !== null && currentCustomerUserData !== undefined && (
                    <EditCustomerUserProfileContent currentCustomerUser={currentCustomerUserData} />
                )}
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                authenticationRequired: true,
                redisClient,
                domainConfig,
                t,
                additionalProps: { uuid: context.params?.uuid },
            }),
);

const getFullName = (firstName?: string | null, lastName?: string | null) => {
    if (!firstName || !lastName) {
        return firstName ?? lastName;
    }

    return `${firstName} ${lastName}`;
};

export default EditUserPage;
