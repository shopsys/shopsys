import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { ComplaintDetailContent } from 'components/Pages/Customer/ComplaintDetail/ComplaintDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    ComplaintQueryDocument,
    TypeComplaintQueryVariables,
    useComplaintQuery,
} from 'graphql/requests/complaints/queries/ComplaintQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

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
                            <PageHero
                                icon={DocumentIcon}
                                title={`${t('Your complaint')} ${complaintData.complaint.number}`}
                                titleTid={TIDs.complaint_detail_number_heading}
                            />

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
        currentCustomerUserPrefetchMode: 'full',
        authenticationConfig: {
            authenticationRequired: true,
            authorizedRoles: [
                TypeCustomerUserRoleEnum.RoleApiComplaintCreation,
                TypeCustomerUserRoleEnum.RoleApiCompanyComplaintsView,
            ],
        },
        prefetchedQueries: [
            { query: ComplaintQueryDocument, variables: { complaintNumber: context.query.complaintNumber } },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default ComplaintDetailPage;
