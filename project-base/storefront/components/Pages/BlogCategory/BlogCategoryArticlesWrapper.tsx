import { BlogArticlesList } from './BlogArticlesList';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { BlogCategoryArticlesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { RefObject } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { useBlogCategoryArticlesData } from 'utils/loadMore/useBlogCategoryArticlesData';
import { mapConnectionEdges } from 'utils/mappers/connection';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
    blogCategoryTotalCount: number;
};

export const BlogCategoryArticlesWrapper: FC<BlogCategoryArticlesWrapperProps> = ({
    uuid,
    paginationScrollTargetRef,
    blogCategoryTotalCount,
}) => {
    const { t } = useTranslation();

    const { blogCategoryArticles, areBlogCategoryArticlesFetching, hasNextPage, isLoadingMoreBlogCategoryArticles } =
        useBlogCategoryArticlesData(BlogCategoryArticlesDocument, uuid, blogCategoryTotalCount);

    const mappedArticles = mapConnectionEdges<TypeListedBlogArticleFragment>(blogCategoryArticles);

    const articlesContent = mappedArticles?.length ? (
        <BlogArticlesList
            blogArticles={mappedArticles}
            isLoadingMoreBlogCategoryArticles={isLoadingMoreBlogCategoryArticles}
        />
    ) : (
        <div>{t('Sorry, there are no articles in this category at the moment.')}</div>
    );

    return (
        <div className="flex flex-col gap-y-6 md:gap-y-10">
            {areBlogCategoryArticlesFetching ? (
                <div className="flex flex-col gap-y-5">
                    {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                        <SkeletonModuleArticleBlog key={index} />
                    ))}
                </div>
            ) : (
                articlesContent
            )}

            <Pagination
                isWithLoadMore
                hasNextPage={hasNextPage}
                pageSize={DEFAULT_BLOG_PAGE_SIZE}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={blogCategoryTotalCount}
                type="blog"
            />
        </div>
    );
};
