import { createContext, useState } from 'react';

const initialState = [];

export const SsfwGlobalErrorContext = createContext();

export const SsfwGlobalErrorProvider = ({ children }) => {
    const [state, setState] = useState(initialState);

    return <SsfwGlobalErrorContext.Provider value={{ state, setState }}>{children}</SsfwGlobalErrorContext.Provider>;
};
