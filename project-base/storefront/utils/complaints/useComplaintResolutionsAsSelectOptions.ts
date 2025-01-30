import { TypeComplaintResolutionFragment } from 'graphql/requests/complaints/fragments/ComplaintResolutionFragment.generated';
import { useComplaintResolutionQuery } from 'graphql/requests/complaints/queries/ComplaintResolutionQuery.generated';
import { useMemo } from 'react';
import { SelectOptionType } from 'types/selectOptions';

export const useComplaintResolutionsAsSelectOptions = () => {
    const [{ data: complaintResolutionData }] = useComplaintResolutionQuery();

    return useMemo(
        () => mapComplaintsToSelectOptions(complaintResolutionData?.complaintResolution),
        [complaintResolutionData?.complaintResolution],
    );
};

const mapComplaintsToSelectOptions = (countries: TypeComplaintResolutionFragment[] | undefined): SelectOptionType[] =>
    countries?.map((resolution) => ({ label: resolution.name, value: resolution.value })) ?? [];
