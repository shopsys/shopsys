import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { CustomerDashboard } from 'components/Pages/Customer/CustomerDashboard';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const CustomerPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [dashboardUrl] = getInternationalizedStaticUrls(['/customer'], url);
    const title = t('Account overview');
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: title, slug: dashboardUrl }];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CustomerLayout breadcrumbs={breadcrumbs} defaultTitle={title}>
            <CustomerDashboard title={title} />
        </CustomerLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                authenticationConfig: { authenticationRequired: true },
                currentCustomerUserPrefetchMode: 'full',
                redisClient,
                domainConfig,
                t,
            }),
);

export default CustomerPage;
