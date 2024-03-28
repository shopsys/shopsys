import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';

type BlogArticlesListProps = {
    blogArticles: TypeListedBlogArticleFragment[];
};

export const BlogArticlesList: FC<BlogArticlesListProps> = ({ blogArticles }) => {
    const { defaultLocale } = useDomainConfig();

    return (
        <ul className="flex w-full flex-col flex-wrap md:flex-row">
            {blogArticles.map((blogArticle) => (
                <li key={blogArticle.uuid} className="mb-14 flex w-full flex-col p-0 md:flex-row md:gap-x-10">
                    <div className="mb-3 w-full text-center md:mb-0 md:w-48">
                        <ExtendedNextLink href={blogArticle.link} type="blogArticle">
                            <Image
                                alt={blogArticle.mainImage?.name || blogArticle.name}
                                height={600}
                                sizes="(max-width: 600px) 100vw, 20vw"
                                src={blogArticle.mainImage?.url}
                                width={600}
                            />
                        </ExtendedNextLink>
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

                        <ExtendedNextLink
                            className="group hover:no-underline"
                            href={blogArticle.link}
                            type="blogArticle"
                        >
                            <h2 className="mb-3 group-hover:text-primary">{blogArticle.name}</h2>
                        </ExtendedNextLink>

                        {!!blogArticle.perex && <p className="mb-3 text-base">{blogArticle.perex}</p>}

                        <p className="text-sm font-bold">
                            {new Date(blogArticle.publishDate).toLocaleDateString(defaultLocale)}
                        </p>
                    </div>
                </li>
            ))}
        </ul>
    );
};
