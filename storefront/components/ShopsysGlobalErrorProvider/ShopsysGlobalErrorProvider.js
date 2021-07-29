import { createContext, useState } from 'react';

const initialState = [];

export const ShopsysGlobalErrorContext = createContext();

export const ShopsysGlobalErrorProvider = ({ children }) => {
    const [state, setState] = useState(initialState);

    return (
        <ShopsysGlobalErrorContext.Provider value={{ state, setState }}>{children}</ShopsysGlobalErrorContext.Provider>
    );
};
