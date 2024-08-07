import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';

type MainProps = {
    articles: TypeListedBlogArticleFragment[];
};

export const BlogPreviewMain: FC<MainProps> = ({ articles }) => (
    <>
        {articles.map((article) => (
            <ArticleLink
                key={article.uuid}
                href={article.link}
                className={twJoin(
                    'flex-1 flex-col first:flex lg:flex p-5 rounded-md transition-colors no-underline',
                    'bg-backgroundMore',
                    'hover:bg-backgroundMost hover:no-underline',
                )}
            >
                <Image
                    alt={article.mainImage?.name || article.name}
                    className="mx-auto rounded"
                    height={220}
                    sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 35vw"
                    src={article.mainImage?.url}
                    width={700}
                />

                <div className="mt-2 flex flex-col items-start gap-2">
                    {article.blogCategories.map((blogCategory) => (
                        <Fragment key={blogCategory.uuid}>
                            {!!blogCategory.parent && <Flag href={blogCategory.link}>{blogCategory.name}</Flag>}
                        </Fragment>
                    ))}

                    <div className="block text-lg font-bold leading-5">{article.name}</div>

                    <div className="leading-5">{article.perex}</div>
                </div>
            </ArticleLink>
        ))}
    </>
);
