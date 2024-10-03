import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';

type BlogArticlesListProps = {
    blogArticles: TypeListedBlogArticleFragment[];
};

export const BlogArticlesList: FC<BlogArticlesListProps> = ({ blogArticles }) => {
    const { defaultLocale } = useDomainConfig();

    return (
        <ul className="flex w-full flex-col gap-y-5">
            {blogArticles.map((blogArticle) => (
                <li key={blogArticle.uuid} className="w-full">
                    <ExtendedNextLink
                        href={blogArticle.link}
                        type="blogArticle"
                        className={twJoin(
                            'group flex w-full flex-col gap-y-6 rounded-xl border border-backgroundMore p-5 transition-colors md:flex-row md:gap-x-6 md:gap-y-0',
                            'bg-backgroundMore no-underline',
                            'hover:border-borderAccentLess hover:bg-background hover:no-underline',
                        )}
                    >
                        <div className="w-full text-center md:w-[250px] lg:w-[320px]">
                            <Image
                                alt={blogArticle.mainImage?.name || blogArticle.name}
                                className="rounded-xl"
                                height={351}
                                sizes="(max-width: 600px) 100vw, (min-width: 600px) 250px, (min-width: 769px) 320px"
                                src={blogArticle.mainImage?.url}
                                width={510}
                            />
                        </div>

                        <div className="flex flex-1 flex-col gap-y-3">
                            <div className="flex flex-wrap items-center gap-x-6 gap-y-2">
                                <span className="font-secondary text-sm font-semibold text-textSubtle" tid={TIDs.blog_article_publication_date}>
                                    {new Date(blogArticle.publishDate).toLocaleDateString(defaultLocale)}
                                </span>
                                <div className="flex flex-wrap gap-2">
                                    {blogArticle.blogCategories.map((blogArticleCategory) => (
                                        <Fragment key={blogArticleCategory.uuid}>
                                            {blogArticleCategory.parent && (
                                                <Flag href={blogArticleCategory.link} type="blog">
                                                    {blogArticleCategory.name}
                                                </Flag>
                                            )}
                                        </Fragment>
                                    ))}
                                </div>
                            </div>

                            <h2 className="h5 mb-0 !font-bold text-text group-hover:text-link group-hover:underline max-md:text-[15px] max-md:leading-5">
                                {blogArticle.name}
                            </h2>

                            {!!blogArticle.perex && (
                                <p className="mb-0 font-secondary text-base leading-[26px] max-md:text-[14px] max-md:leading-6">
                                    {blogArticle.perex}
                                </p>
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
