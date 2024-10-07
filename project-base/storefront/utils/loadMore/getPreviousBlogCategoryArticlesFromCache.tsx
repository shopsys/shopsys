import { mergeItemEdges } from './mergeItemEdges';
import { readBlogCategoryArticlesFromCache } from './readBlogCategoryArticlesFromCache';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { DocumentNode } from 'graphql';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { Client } from 'urql';

export const getPreviousBlogCategoryArticlesFromCache = (
    queryDocument: DocumentNode,
    client: Client,
    uuid: string,
    pageSize: number,
    initialPageSize: number,
    currentPage: number,
    currentLoadMore: number,
    readBlogCategoryArticles: typeof readBlogCategoryArticlesFromCache,
): TypeBlogArticleConnectionFragment['edges'] | undefined => {
    let cachedPartOfBlogCategoryArticles: TypeBlogArticleConnectionFragment['edges'] | undefined;
    let iterationsCounter = currentLoadMore;

    if (initialPageSize !== pageSize) {
        const offsetEndCursor = getEndCursor(currentPage, 0, DEFAULT_BLOG_PAGE_SIZE);
        const currentCacheSlice = readBlogCategoryArticlesFromCache(
            queryDocument,
            client,
            uuid,
            offsetEndCursor,
            initialPageSize,
        ).blogCategoryArticles;

        if (currentCacheSlice) {
            cachedPartOfBlogCategoryArticles = currentCacheSlice;
            iterationsCounter -= initialPageSize / pageSize;
        } else {
            return undefined;
        }
    }

    while (iterationsCounter > 0) {
        const offsetEndCursor = getEndCursor(
            currentPage + currentLoadMore - iterationsCounter,
            0,
            DEFAULT_BLOG_PAGE_SIZE,
        );
        const currentCacheSlice = readBlogCategoryArticles(
            queryDocument,
            client,
            uuid,
            offsetEndCursor,
            pageSize,
        ).blogCategoryArticles;

        if (currentCacheSlice) {
            if (cachedPartOfBlogCategoryArticles) {
                cachedPartOfBlogCategoryArticles = mergeItemEdges(
                    cachedPartOfBlogCategoryArticles,
                    currentCacheSlice,
                ) as TypeBlogArticleConnectionFragment['edges'];
            } else {
                cachedPartOfBlogCategoryArticles = currentCacheSlice;
            }
        } else {
            return undefined;
        }

        iterationsCounter--;
    }

    return cachedPartOfBlogCategoryArticles;
};
