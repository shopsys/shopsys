import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { ComplaintDetailContent } from 'components/Pages/Customer/ComplaintDetail/ComplaintDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    ComplaintQueryDocument,
    TypeComplaintQueryVariables,
    useComplaintQuery,
} from 'graphql/requests/complaints/queries/ComplaintQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const ComplaintDetailPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerComplaintsUrl] = getInternationalizedStaticUrls(['/customer/complaints'], url);
    const router = useRouter();
    const complaintNumber = getStringFromUrlQuery(router.query.complaintNumber);
    const [{ data: complaintData, fetching: isComplaintDetailFetching, error: complaintDetailError }] =
        useComplaintQuery({
            variables: { complaintNumber },
        });

    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My complaints'), slug: customerComplaintsUrl },
        { __typename: 'Link', name: complaintNumber, slug: '' },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard errorRedirectUrl={customerComplaintsUrl} isWithAccess={!complaintDetailError}>
                <CustomerLayout
                    breadcrumbs={breadcrumbs}
                    breadcrumbsType="complaintList"
                    isFetchingData={isComplaintDetailFetching}
                    title={`${t('Complaint number')} ${complaintNumber}`}
                >
                    {!!complaintData?.complaint && (
                        <>
                            <h1 tid={TIDs.complaint_detail_number_heading}>
                                {t('Your complaint')} {complaintData.complaint.number}
                            </h1>
                            <ComplaintDetailContent complaint={complaintData.complaint} />
                        </>
                    )}
                </CustomerLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (typeof context.query.complaintNumber !== 'string') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps<TypeComplaintQueryVariables>({
        context,
        authenticationRequired: true,
        prefetchedQueries: [
            { query: ComplaintQueryDocument, variables: { complaintNumber: context.query.complaintNumber } },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default ComplaintDetailPage;
