import { calculatePageSize } from './calculatePageSize';
import { getPageSizeInfo } from './getPageSizeInfo';
import { getPreviousBlogCategoryArticlesFromCache } from './getPreviousBlogCategoryArticlesFromCache';
import { hasReadAllItemsFromCache } from './hasReadAllItemsFromCache';
import { mergeItemEdges } from './mergeItemEdges';
import { readBlogCategoryArticlesFromCache } from './readBlogCategoryArticlesFromCache';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { DocumentNode } from 'graphql';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import {
    TypeBlogCategoryArticles,
    TypeBlogCategoryArticlesVariables,
} from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import { useEffect, useRef, useState } from 'react';
import { useClient } from 'urql';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

export const useBlogCategoryArticlesData = (queryDocument: DocumentNode, uuid: string, totalArticlesCount: number) => {
    const client = useClient();
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();

    const previousLoadMoreRef = useRef(currentLoadMore);
    const previousPageRef = useRef(currentPage);
    const initialPageSizeRef = useRef(calculatePageSize(currentLoadMore, DEFAULT_BLOG_PAGE_SIZE));

    const [blogCategoryArticlesData, setBlogCategoryArticlesData] = useState(
        readBlogCategoryArticlesFromCache(
            queryDocument,
            client,
            uuid,
            getEndCursor(currentPage, 0, DEFAULT_BLOG_PAGE_SIZE),
            initialPageSizeRef.current,
        ),
    );

    const [areBlogCategoryArticlesFetching, setAreBlogCategoryArticlesFetching] = useState(
        !blogCategoryArticlesData.blogCategoryArticles,
    );
    const [isLoadingMoreBlogCategoryArticles, setIsLoadingMoreBlogCategoryArticles] = useState(false);

    const fetchBlogCategoryArticles = async (
        variables: TypeBlogCategoryArticlesVariables,
        previouslyQueriedProductsFromCache: TypeBlogArticleConnectionFragment['edges'] | undefined,
    ) => {
        const productsResponse = await client
            .query<TypeBlogCategoryArticles, typeof variables>(queryDocument, variables)
            .toPromise();

        if (!productsResponse.data) {
            setBlogCategoryArticlesData({ blogCategoryArticles: undefined, hasNextPage: false });

            return;
        }

        setBlogCategoryArticlesData({
            blogCategoryArticles: mergeItemEdges(
                previouslyQueriedProductsFromCache,
                productsResponse.data.blogCategory?.blogArticles.edges,
            ) as TypeBlogArticleConnectionFragment['edges'],
            hasNextPage: productsResponse.data.blogCategory?.blogArticles.pageInfo.hasNextPage ?? false,
        });
        stopFetching();
    };

    const startFetching = () => {
        if (previousLoadMoreRef.current === currentLoadMore || currentLoadMore === 0) {
            setAreBlogCategoryArticlesFetching(true);
        } else {
            setIsLoadingMoreBlogCategoryArticles(true);
            previousLoadMoreRef.current = currentLoadMore;
        }
    };

    const stopFetching = () => {
        setAreBlogCategoryArticlesFetching(false);
        setIsLoadingMoreBlogCategoryArticles(false);
    };

    useEffect(() => {
        if (previousPageRef.current !== currentPage) {
            previousPageRef.current = currentPage;
            initialPageSizeRef.current = DEFAULT_BLOG_PAGE_SIZE;
        }

        const previousProductsFromCache = getPreviousBlogCategoryArticlesFromCache(
            queryDocument,
            client,
            uuid,
            DEFAULT_BLOG_PAGE_SIZE,
            initialPageSizeRef.current,
            currentPage,
            currentLoadMore,
            readBlogCategoryArticlesFromCache,
        );

        if (
            hasReadAllItemsFromCache(
                previousProductsFromCache?.length,
                currentLoadMore,
                currentPage,
                totalArticlesCount,
                DEFAULT_BLOG_PAGE_SIZE,
            )
        ) {
            return;
        }

        const { pageSize, isMoreThanOnePage } = getPageSizeInfo(
            !!previousProductsFromCache,
            currentLoadMore,
            DEFAULT_BLOG_PAGE_SIZE,
        );
        const endCursor = getEndCursor(
            currentPage,
            isMoreThanOnePage ? undefined : currentLoadMore,
            DEFAULT_BLOG_PAGE_SIZE,
        );

        startFetching();
        fetchBlogCategoryArticles(
            {
                endCursor,
                uuid,
                pageSize,
            },
            previousProductsFromCache,
        );
    }, [uuid, currentPage, currentLoadMore]);

    return { ...blogCategoryArticlesData, areBlogCategoryArticlesFetching, isLoadingMoreBlogCategoryArticles };
};
