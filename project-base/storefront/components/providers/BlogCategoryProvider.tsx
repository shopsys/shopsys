'use client';

import { createContext, RefObject, useContext } from 'react';

type BlogCategoryOverview = {
    paginationScrollTargetRef: RefObject<HTMLDivElement | null>;
    blogCategoryUuid: string;
};
export const BlogCategoryContext = createContext<BlogCategoryOverview | undefined>(undefined);

type BlogCategoryProviderProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement | null>;
    blogCategoryUuid: string;
};

export const BlogCategoryProvider: FC<BlogCategoryProviderProps> = ({
    paginationScrollTargetRef,
    blogCategoryUuid,
    children,
}) => {
    return (
        <BlogCategoryContext.Provider
            value={{
                paginationScrollTargetRef,
                blogCategoryUuid,
            }}
        >
            {children}
        </BlogCategoryContext.Provider>
    );
};

export const useBlogCategory = () => {
    const blogCategoryData = useContext(BlogCategoryContext);
    if (!blogCategoryData) {
        throw new Error(`useBlogCategory must be use within BlogCategoryProvider`);
    }
    return blogCategoryData;
};
