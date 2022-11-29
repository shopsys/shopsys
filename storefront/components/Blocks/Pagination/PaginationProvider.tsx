import { PaginationContext } from './context';
import { paginationReducer } from './reducer';
import { FC, useReducer } from 'react';

type Props = {
    endCursor?: string;
    page: number;
};

export const PaginationProvider: FC<Props> = ({ children, endCursor, page }) => {
    const value = useReducer(paginationReducer, {
        endCursor,
        page,
    });

    return <PaginationContext.Provider value={value}>{children}</PaginationContext.Provider>;
};
