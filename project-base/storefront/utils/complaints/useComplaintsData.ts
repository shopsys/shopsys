import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { MINIMAL_SEARCH_QUERY_LENGTH } from 'components/Layout/Header/AutocompleteSearch/constants';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
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
            first: DEFAULT_PAGE_SIZE,
            after: isSearchQueryValid ? null : getEndCursor(currentPage),
            searchInput: {
                parameters: [],
                search: isSearchQueryValid ? debouncedSearchQuery : '',
                isAutocomplete: false,
                userIdentifier,
            },
        },
    });

    const mappedComplaints = mapConnectionEdges<TypeComplaintDetailFragment>(complaintsData?.complaints.edges);
    const complaintsTotalCount = complaintsData?.complaints.totalCount;

    return { mappedComplaints, complaintsTotalCount, complaintsDataFetching };
};
