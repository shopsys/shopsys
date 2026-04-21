import { TypeSimpleCustomerUserFragment } from 'graphql/requests/customer/fragments/SimpleCustomerUserFragment.generated';
import { CustomerUserType } from 'types/customer';

export const getCustomerUser = (customerUser: TypeSimpleCustomerUserFragment | undefined): CustomerUserType => {
    return {
        ...customerUser,
        firstName: customerUser?.firstName ?? '',
        lastName: customerUser?.lastName ?? '',
        telephone: customerUser?.telephone ?? '',
        telephonePrefix: customerUser?.telephoneData?.prefix ?? '',
        telephonePrefixCountryCode: customerUser?.telephoneData?.countryCode ?? '',
        telephoneNumber: customerUser?.telephoneData?.number ?? '',
        email: customerUser?.email ?? '',
    };
};
