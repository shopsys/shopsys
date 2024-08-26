import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeOrdersQueryVariables } from 'graphql/requests/orders/queries/OrdersQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const ComplaintsPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerComplaintsUrl] = getInternationalizedStaticUrls(['/customer/complaints'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My complaints'), slug: customerComplaintsUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [customerComplaintsNewlUrl] = getInternationalizedStaticUrls(['/customer/complaints/new-complaint'], url);
    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('My complaints')} title={t('My complaints')}>
                <LinkButton
                    size="small"
                    type="complaint"
                    href={{
                        pathname: customerComplaintsNewlUrl,
                    }}
                >
                    {t('New complaint')}
                </LinkButton>
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    return initServerSideProps<TypeOrdersQueryVariables>({
        context,
        authenticationRequired: true,
        redisClient,
        domainConfig,
        t,
    });
});

export default ComplaintsPage;
