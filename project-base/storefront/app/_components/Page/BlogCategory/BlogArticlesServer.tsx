import { Flag } from 'app/_components/Basic/Flag/Flag';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.ssr';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.ssr';
import { defaultLocale } from 'i18n';
import { notFound } from 'next/navigation';
import { twJoin } from 'tailwind-merge';
import { mapConnectionEdges } from 'utils/mappers/connection';

export type BlogArticlesProps = {
    blogArticlesPromise: Promise<TypeBlogArticleConnectionFragment | undefined>;
};

export const BlogArticlesServer = async ({ blogArticlesPromise }: BlogArticlesProps) => {
    const t = await getTranslation();
    const blogArticleConnection = await blogArticlesPromise;

    if (!blogArticleConnection) {
        return notFound();
    }

    const mappedArticles = mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticleConnection.edges);

    if (!mappedArticles?.length) {
        return <div>{t('Sorry, there are no articles in this category at the moment.')}</div>;
    }

    return (
        <>
            {mappedArticles.map((blogArticle) => (
                <li key={blogArticle.uuid} className="w-full xl:max-w-[784px]">
                    <ExtendedNextLink
                        href={blogArticle.link}
                        type="blogArticle"
                        className={twJoin(
                            'border-backgroundMore group flex w-full flex-col gap-y-6 rounded-xl border p-5 transition-colors md:flex-row md:gap-x-6 md:gap-y-0',
                            'bg-backgroundMore no-underline',
                            'hover:border-borderAccentLess hover:bg-background hover:no-underline',
                        )}
                    >
                        <div className="w-full text-center md:w-[250px] lg:w-80">
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
                                <span
                                    className="font-secondary text-textSubtle text-sm font-semibold"
                                    tid={TIDs.blog_article_publication_date}
                                >
                                    {new Date(blogArticle.publishDate).toLocaleDateString(defaultLocale)}
                                </span>
                                <div className="flex flex-wrap gap-2">
                                    {blogArticle.blogCategories.map((blogArticleCategory) => (
                                        <>
                                            {blogArticleCategory.parent && (
                                                <Flag
                                                    key={blogArticleCategory.uuid}
                                                    href={blogArticleCategory.link}
                                                    type="blog"
                                                >
                                                    {blogArticleCategory.name}
                                                </Flag>
                                            )}
                                        </>
                                    ))}
                                </div>
                            </div>

                            <h2 className="h5 text-text group-hover:text-link mb-0 !font-bold group-hover:underline max-md:text-base">
                                {blogArticle.name}
                            </h2>

                            {!!blogArticle.perex && (
                                <p className="font-secondary mb-0 text-base max-md:text-sm">{blogArticle.perex}</p>
                            )}
                        </div>
                    </ExtendedNextLink>
                </li>
            ))}
        </>
    );
};
