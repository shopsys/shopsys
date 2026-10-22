import { useCustomerUserRoleGroupsQuery } from 'graphql/requests/customer/queries/CustomerUserRoleGroupsQuery.generated';
import { TypeCustomerUserRoleGroup } from 'graphql/types';
import { RadiobuttonOptionType } from 'types/radiobuttonOptions';
import { useRedirectOnPermissionsChange } from './useRedirectOnPermissionsChange';

type RoleGroupOption = Pick<TypeCustomerUserRoleGroup, 'uuid' | 'name'>;

export const useCustomerUserGroupsAsRadiobuttonOptions = (
    isDisabled: boolean,
    editedCustomerUserRoleGroup?: RoleGroupOption,
): {
    customerUserRoleGroupsOptions: RadiobuttonOptionType[];
    isFetching: boolean;
} => {
    const [{ data: customerUserRoleGroupsData, error, fetching: isFetching }] = useCustomerUserRoleGroupsQuery({
        requestPolicy: 'cache-and-network',
    });
    const { redirect } = useRedirectOnPermissionsChange();

    const customerUserRoleGroupsOptions = mapUserGroupsToRadiobuttonOptions(
        getRoleGroupsIncludingEditedCustomerUserRoleGroup(
            customerUserRoleGroupsData?.customerUserRoleGroups,
            editedCustomerUserRoleGroup,
        ),
        isDisabled,
    );

    if (error?.networkError?.message.includes('No Content')) {
        redirect();
    }

    return {
        customerUserRoleGroupsOptions,
        isFetching,
    };
};

const getRoleGroupsIncludingEditedCustomerUserRoleGroup = (
    groups: RoleGroupOption[] | undefined,
    editedCustomerUserRoleGroup: RoleGroupOption | undefined,
): RoleGroupOption[] | undefined => {
    if (
        !groups ||
        !editedCustomerUserRoleGroup ||
        groups.some((group) => group.uuid === editedCustomerUserRoleGroup.uuid)
    ) {
        return groups;
    }

    return [...groups, editedCustomerUserRoleGroup];
};

const mapUserGroupsToRadiobuttonOptions = (
    groups: RoleGroupOption[] | undefined,
    isDisabled: boolean,
): RadiobuttonOptionType[] =>
    groups?.map((group) => ({ label: group.name, value: group.uuid, disabled: isDisabled })) ?? [];
