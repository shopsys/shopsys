'use client';

import { LoadMoreBlogCategoryArticlesAction } from './LoadMoreBlogCategoryArticlesAction';
import { useCurrentLoadMoreQuery } from 'app/_utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'app/_utils/queryParams/useCurrentPageQuery';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { ReactNode, Suspense, use, useMemo, useRef } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

type ClientBlogArticlesProps = {
    blogCategoryUuid: string;
    initialPage: number;
    initialLoadMore: number;
};

export const ClientBlogArticles = ({ blogCategoryUuid, initialLoadMore }: ClientBlogArticlesProps) => {
    // Get current page and load more values from URL
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();

    // Use a ref to cache promises to prevent recreating them on every render
    const promiseCacheRef = useRef(new Map<string, Promise<ReactNode>>());

    // Only fetch additional articles beyond what the server already fetched
    const shouldFetchAdditional = currentLoadMore > initialLoadMore;

    // Generate the range of load more values we need to fetch
    const loadMoreRange = useMemo(() => {
        const range = [];
        if (shouldFetchAdditional) {
            for (let lm = initialLoadMore + 1; lm <= currentLoadMore; lm++) {
                range.push(lm);
            }
        }
        return range;
    }, [initialLoadMore, currentLoadMore, shouldFetchAdditional]);

    // If no additional articles to fetch, return null
    if (loadMoreRange.length === 0) {
        return null;
    }

    return (
        <>
            {loadMoreRange.map((lm) => {
                // Create a unique key for this request
                const cacheKey = `${blogCategoryUuid}-page:${currentPage}-lm:${lm}`;

                // Create or retrieve the promise for this request
                if (!promiseCacheRef.current.has(cacheKey)) {
                    const promise = LoadMoreBlogCategoryArticlesAction(blogCategoryUuid, currentPage, lm);
                    promiseCacheRef.current.set(cacheKey, promise);
                }

                // Get the promise from the cache
                const promise = promiseCacheRef.current.get(cacheKey)!;

                return (
                    <Suspense
                        key={cacheKey}
                        fallback={
                            <div className="flex flex-col gap-y-5">
                                {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                                    <SkeletonModuleArticleBlog key={cacheKey + index} />
                                ))}
                            </div>
                        }
                    >
                        <ArticleRenderer promise={promise} />
                    </Suspense>
                );
            })}
        </>
    );
};

// This component uses the `use` hook to read the promise value
const ArticleRenderer = ({ promise }: { promise: Promise<ReactNode> }) => {
    // Use the `use` hook to read the promise value
    const articleNode = use(promise);

    // If no article node, return null
    if (!articleNode) {
        return null;
    }

    // Return the article node directly
    return articleNode;
};
