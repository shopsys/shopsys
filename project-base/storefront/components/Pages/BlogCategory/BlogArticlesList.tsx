import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';

type BlogArticlesListProps = {
    blogArticles: TypeListedBlogArticleFragment[];
};

export const BlogArticlesList: FC<BlogArticlesListProps> = ({ blogArticles }) => {
    const { defaultLocale } = useDomainConfig();

    return (
        <ul className="flex w-full flex-col flex-wrap md:flex-row">
            {blogArticles.map((blogArticle) => (
                <li key={blogArticle.uuid} className="mb-5 w-full">
                    <ExtendedNextLink
                        href={blogArticle.link}
                        type="blogArticle"
                        className={twJoin(
                            'flex w-full flex-col p-4 rounded md:flex-row md:gap-x-10 transition-colors',
                            'bg-backgroundMore no-underline',
                            'hover:bg-backgroundMost hover:no-underline',
                        )}
                    >
                        <div className="mb-3 w-full text-center md:mb-0 md:w-48">
                            <Image
                                alt={blogArticle.mainImage?.name || blogArticle.name}
                                height={600}
                                sizes="(max-width: 600px) 100vw, 20vw"
                                src={blogArticle.mainImage?.url}
                                width={600}
                            />
                        </div>

                        <div className="flex flex-1 flex-col">
                            <div>
                                {blogArticle.blogCategories.map((blogArticleCategory) => (
                                    <Fragment key={blogArticleCategory.uuid}>
                                        {blogArticleCategory.parent && (
                                            <Flag href={blogArticleCategory.link}>{blogArticleCategory.name}</Flag>
                                        )}
                                    </Fragment>
                                ))}
                            </div>

                            <h2 className="mb-3">{blogArticle.name}</h2>

                            {!!blogArticle.perex && <p className="mb-3 text-base">{blogArticle.perex}</p>}

                            <p className="text-sm font-bold">
                                {new Date(blogArticle.publishDate).toLocaleDateString(defaultLocale)}
                            </p>
                        </div>
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    );
};
