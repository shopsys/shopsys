import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { BlogCategoryArticlesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useBlogCategoryArticlesData } from 'utils/loadMore/useBlogCategoryArticlesData';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { BlogArticlesList } from './BlogArticlesList';

type BlogCategoryArticlesWrapperProps = {
    uuid: string;
    paginationScrollTargetRef: React.RefObject<HTMLDivElement | null>;
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
        <div ref={paginationScrollTargetRef}>
            {areBlogCategoryArticlesFetching ? (
                <div className="flex flex-1 flex-col gap-y-5">
                    {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                        <SkeletonModuleArticleBlog key={index} />
                    ))}
                </div>
            ) : (
                articlesContent
            )}

            <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                <Pagination
                    isWithLoadMore
                    hasNextPage={hasNextPage}
                    pageSize={DEFAULT_BLOG_PAGE_SIZE}
                    totalCount={blogCategoryTotalCount}
                    type="blog"
                />
            </PaginationProvider>
        </div>
    );
};
