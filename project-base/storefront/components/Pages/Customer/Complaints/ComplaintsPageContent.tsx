import { ComplaintsContent } from 'components/Pages/Customer/Complaints/ComplaintsContent';
import { ComplaintsFilter } from 'components/Pages/Customer/Complaints/ComplaintsFilter';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRouter } from 'next/router';
import { type RefObject } from 'react';
import {
    getComplaintStatusCodeFromUrlQuery,
    getComplaintsFilterFromUrlQuery,
    getComplaintsStatuslessFilterFromUrlQuery,
    hasActiveComplaintListFiltersFromUrlQuery,
} from 'utils/complaints/getComplaintsFilterFromUrlQuery';
import { useComplaintsData } from 'utils/complaints/useComplaintsData';
import { COMPLAINT_STATUS_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

type ComplaintsPageContentProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement | null>;
};

export const ComplaintsPageContent: FC<ComplaintsPageContentProps> = ({ paginationScrollTargetRef }) => {
    const router = useRouter();
    const { fallbackTimezone } = useDomainConfig();
    const filter = getComplaintsFilterFromUrlQuery(router.query, fallbackTimezone);
    const activeStatusCode = getComplaintStatusCodeFromUrlQuery(router.query[COMPLAINT_STATUS_QUERY_PARAMETER_NAME]);
    const hasActiveFilters = hasActiveComplaintListFiltersFromUrlQuery(router.query);
    const statuslessFilter = getComplaintsStatuslessFilterFromUrlQuery(router.query, fallbackTimezone);
    const { mappedComplaints, complaintsTotalCount, complaintStatusCounts, complaintsDataFetching, complaintsData } =
        useComplaintsData(filter, statuslessFilter);

    return (
        <div className="flex scroll-mt-fixed-header flex-col gap-5" ref={paginationScrollTargetRef}>
            <ComplaintsFilter complaintStatusCounts={complaintStatusCounts} />

            <ComplaintsContent
                areComplaintsFetching={complaintsDataFetching}
                filteredTotalCount={complaintsTotalCount}
                hasActiveFilters={hasActiveFilters}
                hasActiveStatus={activeStatusCode !== null}
                hasNextPage={complaintsData?.complaints.pageInfo.hasNextPage}
                items={mappedComplaints}
            />
        </div>
    );
};
