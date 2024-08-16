import { useRedirectOnPermissionsChange } from './useRedirectOnPermissionsChange';
import { useCustomerUserRoleGroupsQuery } from 'graphql/requests/customer/queries/CustomerUserRoleGroupsQuery.generated';
import { TypeCustomerUserRoleGroup } from 'graphql/types';
import { useMemo } from 'react';
import { SelectOptionType } from 'types/selectOptions';

export const useCustomerUserGroupsAsSelectOptions = () => {
    const [{ data: customerUserRoleGroupsData, error }] = useCustomerUserRoleGroupsQuery({
        requestPolicy: 'cache-and-network',
    });
    const { redirect } = useRedirectOnPermissionsChange();

    const customerUserRoleGroupsDataMemoized = useMemo(
        () => mapuserGroupsToSelectOptions(customerUserRoleGroupsData?.customerUserRoleGroups),
        [customerUserRoleGroupsData?.customerUserRoleGroups],
    );

    if (error?.networkError && error.networkError.message.includes('No Content')) {
        redirect();
    }

    return customerUserRoleGroupsDataMemoized;
};

const mapuserGroupsToSelectOptions = (groups: TypeCustomerUserRoleGroup[] | undefined): SelectOptionType[] =>
    groups?.map((group) => ({ label: group.name, value: group.uuid })) ?? [];
