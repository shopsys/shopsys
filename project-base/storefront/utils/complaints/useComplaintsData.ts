import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { MINIMAL_SEARCH_QUERY_LENGTH } from 'components/Layout/Header/AutocompleteSearch/constants';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeComplaintListItemFragment } from 'graphql/requests/complaints/fragments/ComplaintListItemFragment.generated';
import { useComplaintsQuery } from 'graphql/requests/complaints/queries/ComplaintsQuery.generated';
import { useCookiesStore } from 'store/useCookiesStore';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useDebounce } from 'utils/useDebounce';

export const useComplaintsData = (searchQueryValue: string) => {
    const currentPage = useCurrentPageQuery();
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const debouncedSearchQuery = useDebounce(searchQueryValue, 300);
    const isSearchQueryValid = debouncedSearchQuery.length >= MINIMAL_SEARCH_QUERY_LENGTH;

    const [{ data: complaintsData, fetching: complaintsDataFetching }] = useComplaintsQuery({
        variables: {
            first: DEFAULT_ORDERS_SIZE,
            after: isSearchQueryValid ? null : getEndCursor(currentPage, 0, DEFAULT_ORDERS_SIZE),
            // { after: getEndCursor(currentPage, 0, DEFAULT_ORDERS_SIZE), first: DEFAULT_ORDERS_SIZE }
            searchInput: {
                parameters: [],
                search: isSearchQueryValid ? debouncedSearchQuery : '',
                isAutocomplete: false,
                userIdentifier,
            },
        },
    });

    const mappedComplaints = mapConnectionEdges<TypeComplaintListItemFragment>(complaintsData?.complaints.edges);
    const complaintsTotalCount = complaintsData?.complaints.totalCount;

    return { mappedComplaints, complaintsTotalCount, complaintsDataFetching, complaintsData };
};
