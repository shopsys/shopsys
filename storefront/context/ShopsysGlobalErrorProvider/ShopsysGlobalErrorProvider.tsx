import { createContext, FC, useState } from 'react';

type GlobalErrorContext = {
    errors: string[];
    actions: {
        setErrors: (errors: string[]) => void;
    };
};

export const ShopsysGlobalErrorContext = createContext<GlobalErrorContext>({
    errors: [],
    actions: {
        setErrors: () => undefined,
    },
});

const ShopsysGlobalErrorProvider: FC = ({ children }) => {
    const [errorState, setErrorState] = useState<GlobalErrorContext>({
        errors: [],
        actions: {
            setErrors: (updatedErrors: string[]) => {
                setErrorState((errorState) => {
                    return { ...errorState, errors: updatedErrors };
                });
            },
        },
    });

    return <ShopsysGlobalErrorContext.Provider value={errorState}>{children}</ShopsysGlobalErrorContext.Provider>;
};

/* @component */
export default ShopsysGlobalErrorProvider;
