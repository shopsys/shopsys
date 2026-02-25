import {
    TypeCurrentCustomerUserQuery,
    useCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { createContext, useContext } from 'react';

const CurrentCustomerUserQueryContext = createContext<TypeCurrentCustomerUserQuery | undefined>(undefined);

export const CurrentCustomerUserProvider: FC = ({ children }) => {
    const [{ data }] = useCurrentCustomerUserQuery();

    return <CurrentCustomerUserQueryContext.Provider value={data}>{children}</CurrentCustomerUserQueryContext.Provider>;
};

export const useCurrentCustomerUserQueryData = (): TypeCurrentCustomerUserQuery | undefined => {
    return useContext(CurrentCustomerUserQueryContext);
};
