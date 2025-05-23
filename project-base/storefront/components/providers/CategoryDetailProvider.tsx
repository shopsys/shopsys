'use client';

import { createContext, RefObject, useContext } from 'react';

type CategoryDetailOverview = {
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
    categoryDetailUuid: string;
};
export const CategoryDetailContext = createContext<CategoryDetailOverview | undefined>(undefined);

type CategoryDetailProviderProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
    categoryDetailUuid: string;
};

export const CategoryDetailProvider: FC<CategoryDetailProviderProps> = ({
    paginationScrollTargetRef,
    categoryDetailUuid,
    children,
}) => {
    return (
        <CategoryDetailContext.Provider
            value={{
                paginationScrollTargetRef,
                categoryDetailUuid,
            }}
        >
            {children}
        </CategoryDetailContext.Provider>
    );
};

export const useCategoryDetail = () => {
    const categoryDetailData = useContext(CategoryDetailContext);
    if (!categoryDetailData) {
        throw new Error(`useCategoryDetail must be use within CategoryDetailProvider`);
    }
    return categoryDetailData;
};
