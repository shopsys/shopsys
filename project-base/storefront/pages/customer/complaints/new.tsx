import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { MINIMAL_SEARCH_QUERY_LENGTH } from 'components/Layout/Header/AutocompleteSearch/constants';
import { OrderedItemsContent } from 'components/Pages/Customer/Complaints/OrderedItemsContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_ORDERED_ITEMS_FILTER, DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    OrderedItemsQueryDocument,
    TypeOrderedItemsQueryVariables,
    useOrderedItemsQuery,
} from 'graphql/requests/complaints/queries/OrderedItemsQuery.generated';
import { useSearchOrderedItemsQuery } from 'graphql/requests/complaints/queries/SearchOrderedItemsQuery.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
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

const NewComplaintPage: FC = () => {
    const { t } = useTranslation();
    const currentPage = useCurrentPageQuery();
    const { url } = useDomainConfig();
    const [customerComplaintsUrl, customerComplaintsNewUrl] = getInternationalizedStaticUrls(
        ['/customer/complaints', '/customer/complaints/new'],
        url,
    );
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const [searchQueryValue, setSearchQueryValue] = useState('');
    const debouncedSearchQuery = useDebounce(searchQueryValue, 300);

    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('My complaints'), slug: customerComplaintsUrl },
        { __typename: 'Link', name: t('New complaint'), slug: customerComplaintsNewUrl },
    ];

    const [{ data: orderedItemsData, fetching: orderedItemsFetching }] = useOrderedItemsQuery({
        variables: {
            first: DEFAULT_PAGE_SIZE,
            after: getEndCursor(currentPage),
            filter: DEFAULT_ORDERED_ITEMS_FILTER,
        },
    });

    const [{ data: searchOrderedItemsData, fetching: searchOrderedItemsDataFetching }] = useSearchOrderedItemsQuery({
        variables: {
            first: DEFAULT_PAGE_SIZE,
            after: getEndCursor(currentPage),
            filter: DEFAULT_ORDERED_ITEMS_FILTER,
            searchInput: {
                parameters: [],
                search: debouncedSearchQuery,
                isAutocomplete: false,
                userIdentifier,
            },
        },
        pause: debouncedSearchQuery.length < MINIMAL_SEARCH_QUERY_LENGTH,
        requestPolicy: 'network-only',
    });

    const mappedOrderedItems = mapConnectionEdges<TypeOrderDetailItemFragment>(
        debouncedSearchQuery ? searchOrderedItemsData?.orderItemsSearch.edges : orderedItemsData?.orderItems.edges,
    );

    const orderedItemsTotalCount = debouncedSearchQuery
        ? searchOrderedItemsData?.orderItemsSearch.totalCount
        : orderedItemsData?.orderItems.totalCount;

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CustomerLayout breadcrumbs={breadcrumbs} pageHeading={t('New complaint')} title={t('New complaint')}>
                <div className="mb-5">
                    <SearchInput
                        className="w-full border border-inputBorder"
                        label={t('Search for a product you want to complain about')}
                        shouldShowSpinnerInInput={searchOrderedItemsDataFetching}
                        value={searchQueryValue}
                        onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                        onClear={() => setSearchQueryValue('')}
                    />
                </div>
                <OrderedItemsContent
                    isFetching={orderedItemsFetching || searchOrderedItemsDataFetching}
                    items={mappedOrderedItems}
                    totalCount={orderedItemsTotalCount}
                />
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

    return initServerSideProps<TypeOrderedItemsQueryVariables>({
        context,
        authenticationRequired: true,
        prefetchedQueries: [
            {
                query: OrderedItemsQueryDocument,
                variables: {
                    after: getEndCursor(page),
                    first: DEFAULT_PAGE_SIZE,
                    filter: DEFAULT_ORDERED_ITEMS_FILTER,
                },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default NewComplaintPage;
