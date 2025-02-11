import { TypeComplaintResolutionFragment } from 'graphql/requests/complaints/fragments/ComplaintResolutionFragment.generated';
import { SelectOptionType } from 'types/selectOptions';

export const isResolutionMoneyReturn = (
    resolution: TypeComplaintResolutionFragment | SelectOptionType | undefined,
): boolean => resolution?.value === 'money_return';
