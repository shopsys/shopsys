'use client';

import { createContext, useContext } from 'react';
import { CurrentCustomerType } from 'types/customer';

export const AuthContext = createContext<CurrentCustomerType | undefined>(undefined);

type AuthProviderProps = {
    user: CurrentCustomerType | undefined;
};

export const AuthProvider: FC<AuthProviderProps> = ({ user, children }) => {
    return <AuthContext.Provider value={user}>{children}</AuthContext.Provider>;
};

export const useCurrentCustomerData = () => {
    const userData = useContext(AuthContext);

    return userData;
};
