import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

type BlogArticlesListProps = {
    blogArticles: TypeListedBlogArticleFragment[];
    isLoadingMoreBlogCategoryArticles: boolean;
};

export const BlogArticlesList: FC<BlogArticlesListProps> = ({ blogArticles, isLoadingMoreBlogCategoryArticles }) => {
    return (
        <ul className="flex w-full flex-col gap-y-5">
            {blogArticles.map((blogArticle) => (
                <li key={blogArticle.uuid} className="w-full">
                    <ExtendedNextLink
                        href={blogArticle.link}
                        type="blogArticle"
                        className={twJoin(
                            'border-background-more group flex w-full flex-col gap-y-6 rounded-xl border p-5 transition-colors md:flex-row md:gap-x-6 md:gap-y-0',
                            'bg-background-more no-underline',
                            'hover:border-border-less hover:bg-background-default hover:no-underline',
                        )}
                    >
                        <div className="w-full text-center md:w-[250px] lg:w-80">
                            <Image
                                alt={blogArticle.mainImage?.name || blogArticle.name}
                                className="rounded-xl"
                                height={351}
                                sizes="(max-width: 600px) 85vw, (min-width: 768px) 250px, 320px"
                                src={blogArticle.mainImage?.url}
                                width={510}
                            />
                        </div>

                        <div className="flex flex-1 flex-col gap-y-3">
                            <div className="flex flex-wrap items-center gap-x-1.5 gap-y-2">
                                <ArticleDate
                                    className="mr-3.5"
                                    date={blogArticle.publishDate}
                                    tid={TIDs.blog_article_publication_date}
                                />

                                <div className="flex flex-wrap gap-2">
                                    {blogArticle.blogCategories.map((blogArticleCategory) => (
                                        <>
                                            {blogArticleCategory.parent && (
                                                <Flag key={blogArticleCategory.uuid} type="blog">
                                                    {blogArticleCategory.name}
                                                </Flag>
                                            )}
                                        </>
                                    ))}
                                </div>
                            </div>

                            <h2 className="h5 text-text-default group-hover:text-link-default mb-0 !font-bold group-hover:underline max-md:text-base">
                                {blogArticle.name}
                            </h2>

                            {!!blogArticle.perex && (
                                <p className="font-secondary mb-0 text-base max-md:text-sm">{blogArticle.perex}</p>
                            )}
                        </div>
                    </ExtendedNextLink>
                </li>
            ))}

            {isLoadingMoreBlogCategoryArticles && (
                <div className="flex flex-col gap-y-5">
                    {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, index) => (
                        <SkeletonModuleArticleBlog key={index} />
                    ))}
                </div>
            )}
        </ul>
    );
};
