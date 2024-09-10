import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { EditProfileContent } from 'components/Pages/Customer/EditProfileContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const EditProfilePage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerEditProfileUrl] = getInternationalizedStaticUrls(['/customer/edit-profile'], url);
    const currentCustomerUserData = useCurrentCustomerData();
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Edit profile'), slug: customerEditProfileUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('Edit profile')} title={t('Edit profile')}>
                {currentCustomerUserData !== undefined && currentCustomerUserData !== null && (
                    <EditProfileContent currentCustomerUser={currentCustomerUserData} />
                )}
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, authenticationRequired: true, redisClient, domainConfig, t }),
);

export default EditProfilePage;
