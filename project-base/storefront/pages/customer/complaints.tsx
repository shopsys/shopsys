import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { MINIMAL_SEARCH_QUERY_LENGTH } from 'components/Layout/Header/AutocompleteSearch/constants';
import { ComplaintsContent } from 'components/Pages/Customer/Complaints/ComplaintsContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import {
    useComplaintsQuery,
    ComplaintsQueryDocument,
    TypeComplaintsQueryVariables,
} from 'graphql/requests/complaints/queries/ComplaintsQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useDebounce } from 'utils/useDebounce';

const ComplaintsPage: FC = () => {
    const { t } = useTranslation();
    const currentPage = useCurrentPageQuery();
    const { url } = useDomainConfig();
    const [customerComplaintsUrl, customerComplaintsNewUrl] = getInternationalizedStaticUrls(
        ['/customer/complaints', '/customer/new-complaint'],
        url,
    );
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const [searchQueryValue, setSearchQueryValue] = useState('');
    const debouncedSearchQuery = useDebounce(searchQueryValue, 300);

    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My complaints'), slug: customerComplaintsUrl },
    ];

    const [{ data: complaintsData, fetching: complaintsDataFetching }] = useComplaintsQuery({
        variables: {
            first: DEFAULT_PAGE_SIZE,
            after: getEndCursor(currentPage),
            searchInput: {
                parameters: [],
                search: debouncedSearchQuery.length >= MINIMAL_SEARCH_QUERY_LENGTH ? debouncedSearchQuery : '',
                isAutocomplete: false,
                userIdentifier,
            },
        },
    });

    const mappedComplaints = mapConnectionEdges<TypeComplaintDetailFragment>(complaintsData?.complaints.edges);
    const complaintsTotalCount = complaintsData?.complaints.totalCount;

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout
                breadcrumbs={breadcrumbs}
                breadcrumbsType="account"
                pageHeading={t('My complaints')}
                title={t('My complaints')}
            >
                <LinkButton
                    size="small"
                    type="complaintNew"
                    href={{
                        pathname: customerComplaintsNewUrl,
                    }}
                >
                    {t('New complaint')}
                </LinkButton>
                <div className="my-5">
                    <SearchInput
                        className="w-full border border-inputBorder"
                        label={t('Search for a product you want to complain about')}
                        shouldShowSpinnerInInput={complaintsDataFetching}
                        value={searchQueryValue}
                        onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                        onClear={() => setSearchQueryValue('')}
                    />
                </div>
                <ComplaintsContent
                    isFetching={complaintsDataFetching}
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
                authenticationRequired: true,
                prefetchedQueries: [
                    {
                        query: ComplaintsQueryDocument,
                        variables: {
                            first: DEFAULT_PAGE_SIZE,
                            after: getEndCursor(page),
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
