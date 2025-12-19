import { useRedirectOnPermissionsChange } from './useRedirectOnPermissionsChange';
import { useCustomerUserRoleGroupsQuery } from 'graphql/requests/customer/queries/CustomerUserRoleGroupsQuery.generated';
import { TypeCustomerUserRoleGroup } from 'graphql/types';
import { useMemo } from 'react';
import { RadiobuttonOptionType } from 'types/radiobuttonOptions';

export const useCustomerUserGroupsAsRadiobuttonOptions = (
    isDisabled: boolean,
): {
    customerUserRoleGroupsOptions: RadiobuttonOptionType[];
    isFetching: boolean;
} => {
    const [{ data: customerUserRoleGroupsData, error, fetching: isFetching }] = useCustomerUserRoleGroupsQuery({
        requestPolicy: 'cache-and-network',
    });
    const { redirect } = useRedirectOnPermissionsChange();

    const customerUserRoleGroupsDataMemoized = useMemo(
        () => mapUserGroupsToRadiobuttonOptions(customerUserRoleGroupsData?.customerUserRoleGroups, isDisabled),
        [customerUserRoleGroupsData?.customerUserRoleGroups],
    );

    if (error?.networkError && error.networkError.message.includes('No Content')) {
        redirect();
    }

    return {
        customerUserRoleGroupsOptions: customerUserRoleGroupsDataMemoized,
        isFetching,
    };
};

const mapUserGroupsToRadiobuttonOptions = (
    groups: TypeCustomerUserRoleGroup[] | undefined,
    isDisabled: boolean,
): RadiobuttonOptionType[] =>
    groups?.map((group) => ({ label: group.name, value: group.uuid, disabled: isDisabled })) ?? [];
