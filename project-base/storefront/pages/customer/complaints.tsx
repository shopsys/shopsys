import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { CreateComplaintPopupButton } from 'components/Blocks/Complaint/CreateComplaintPopupButton';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { ComplaintsPageContent } from 'components/Pages/Customer/Complaints/ComplaintsPageContent';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TIDs } from 'cypress/tids';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    ComplaintsQueryDocument,
    TypeComplaintsQueryVariables,
} from 'graphql/requests/complaints/queries/ComplaintsQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRef } from 'react';
import {
    getComplaintsFilterFromUrlQuery,
    getComplaintsStatuslessFilterFromUrlQuery,
} from 'utils/complaints/getComplaintsFilterFromUrlQuery';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const ComplaintsPage: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const { canCreateComplaint } = useAuthorization();
    const [customerComplaintsUrl, customerComplaintsNewUrl] = getInternationalizedStaticUrls(
        ['/customer/complaints', '/customer/new-complaint'],
        url,
    );
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My complaints'), slug: customerComplaintsUrl },
    ];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout
                breadcrumbs={breadcrumbs}
                paginationScrollTargetRef={paginationScrollTargetRef}
                title={t('My complaints')}
            >
                <PageHero
                    icon={DocumentIcon}
                    title={t('My complaints')}
                    description={t(
                        'Track all your complaints, monitor resolutions, and receive updates on every status change.',
                    )}
                />

                {canCreateComplaint && (
                    <div className="flex justify-center gap-2">
                        <LinkButton
                            aria-label={t('Go to new complaint page', { ns: 'accessibility' })}
                            size="small"
                            type="complaintNew"
                            href={{
                                pathname: customerComplaintsNewUrl,
                            }}
                        >
                            {t('New complaint')}
                        </LinkButton>

                        <CreateComplaintPopupButton
                            label={t('Create complaint manually')}
                            size="small"
                            variant="secondary"
                            tid={TIDs.complaints_list_create_complaint_manually_button}
                        />
                    </div>
                )}

                <ComplaintsPageContent paginationScrollTargetRef={paginationScrollTargetRef} />
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

    return initServerSideProps<TypeComplaintsQueryVariables>({
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
            {
                query: ComplaintsQueryDocument,
                variables: {
                    first: DEFAULT_ORDERS_SIZE,
                    after: getEndCursor(page, 0, DEFAULT_ORDERS_SIZE),
                    filter: getComplaintsFilterFromUrlQuery(context.query, domainConfig.fallbackTimezone),
                    statuslessFilter: getComplaintsStatuslessFilterFromUrlQuery(
                        context.query,
                        domainConfig.fallbackTimezone,
                    ),
                },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default ComplaintsPage;
