import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeComplaintListItemFragment } from 'graphql/requests/complaints/fragments/ComplaintListItemFragment.generated';
import { useComplaintsQuery } from 'graphql/requests/complaints/queries/ComplaintsQuery.generated';
import { TypeComplaintFilterInput } from 'graphql/types';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

export type ComplaintStatusCount = {
    statusCode: string;
    label: string;
    count: number;
};

export const useComplaintsData = (
    filter: TypeComplaintFilterInput | null,
    statuslessFilter: TypeComplaintFilterInput | null,
) => {
    const currentPage = useCurrentPageQuery();

    const [{ data: complaintsData, fetching: complaintsDataFetching }] = useComplaintsQuery({
        variables: {
            first: DEFAULT_ORDERS_SIZE,
            after: getEndCursor(currentPage, 0, DEFAULT_ORDERS_SIZE),
            filter,
            statuslessFilter,
        },
        requestPolicy: 'cache-and-network',
    });

    const mappedComplaints = mapConnectionEdges<TypeComplaintListItemFragment>(complaintsData?.complaints.edges);
    const complaintsTotalCount = complaintsData?.complaints.totalCount;
    const complaintStatusCounts = (complaintsData?.complaintStatusCounts ?? []).map(({ status, count }) => ({
        statusCode: status.code,
        label: status.name,
        count,
    }));

    return {
        mappedComplaints,
        complaintsTotalCount,
        complaintStatusCounts,
        complaintsDataFetching,
        complaintsData,
    };
};
