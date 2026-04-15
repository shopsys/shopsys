import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { CreateComplaintPopupButton } from 'components/Blocks/Complaint/CreateComplaintPopupButton';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { ComplaintsContent } from 'components/Pages/Customer/Complaints/ComplaintsContent';
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
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useState } from 'react';
import { useComplaintsData } from 'utils/complaints/useComplaintsData';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const ComplaintsPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { canCreateComplaint } = useAuthorization();
    const [customerComplaintsUrl, customerComplaintsNewUrl] = getInternationalizedStaticUrls(
        ['/customer/complaints', '/customer/new-complaint'],
        url,
    );

    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My complaints'), slug: customerComplaintsUrl },
    ];

    const [searchQueryValue, setSearchQueryValue] = useState('');
    const { mappedComplaints, complaintsTotalCount, complaintsDataFetching, complaintsData } =
        useComplaintsData(searchQueryValue);

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout breadcrumbs={breadcrumbs} title={t('My complaints')}>
                <PageHero
                    icon={ComplaintsIcon}
                    title={t('My complaints')}
                    description={t(
                        'Track all your complaints, monitor resolutions, and receive updates on every status change.',
                    )}
                />

                {canCreateComplaint && (
                    <div className="flex justify-center gap-y-2">
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
                            style={{ marginLeft: '10px' }}
                            tid={TIDs.complaints_list_create_complaint_manually_button}
                        />
                    </div>
                )}

                <SearchInput
                    ariaLabelForSearchButton={t('Search for a product you complained about', { ns: 'accessibility' })}
                    className="w-full border border-input-border-default"
                    label={t('Search for a product you complained about')}
                    shouldShowSpinnerInInput={complaintsDataFetching}
                    value={searchQueryValue}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
                />

                <ComplaintsContent
                    areComplaintsFetching={complaintsDataFetching}
                    hasNextPage={complaintsData?.complaints.pageInfo.hasNextPage}
                    items={mappedComplaints}
                    totalCount={complaintsTotalCount}
                />
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, cookiesStoreState }) =>
        async (context) => {
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

            return initServerSideProps<TypeComplaintsQueryVariables>({
                context,
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
                            searchInput: {
                                parameters: [],
                                search: '',
                                isAutocomplete: false,
                                userIdentifier: cookiesStoreState.userIdentifier,
                            },
                        },
                    },
                ],
                redisClient,
                domainConfig,
                t,
            });
        },
);

export default ComplaintsPage;
