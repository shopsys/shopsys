import { ContactInformationFormType } from 'types/form';
import { useCurrentCustomerContactInformationQuery } from 'connectors/customer/CurrentCustomerUser';
import { useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';

export const useCurrentUserContactInformation = (): ContactInformationFormType => {
    const apiData = useCurrentCustomerContactInformationQuery();
    const reduxData = useShopsysSelector((state) => state.contactInformation);

    return useMemo(() => apiData ?? reduxData, [apiData, reduxData]);
};
