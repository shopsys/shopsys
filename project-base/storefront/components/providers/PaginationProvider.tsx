import { createContext, useContext } from 'react';
import { RefObject } from 'react';

type PaginationContextType = {
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
};
export const PaginationContext = createContext<PaginationContextType | null>(null);

type PaginationProviderProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
};

export const PaginationProvider: FC<PaginationProviderProps> = ({ paginationScrollTargetRef, children }) => {
    return <PaginationContext.Provider value={{ paginationScrollTargetRef }}>{children}</PaginationContext.Provider>;
};

export const usePaginationContext = () => {
    const paginationContext = useContext(PaginationContext);

    if (!paginationContext) {
        throw new Error(`usePaginationContext must be use within PaginationProvider`);
    }

    return paginationContext;
};
