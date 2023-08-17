import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { ListedBlogArticleFragmentApi } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import { ArticleLink } from './BlogPreviewElements';

type MainProps = {
    articles: ListedBlogArticleFragmentApi[];
};

export const BlogPreviewMain: FC<MainProps> = ({ articles }) => (
    <>
        {articles.map((article) => (
            <div
                key={article.uuid}
                className="hidden flex-1 flex-col text-white no-underline first:flex hover:text-white hover:no-underline lg:flex"
            >
                <ArticleLink
                    href={article.link}
                    className="block text-lg font-bold leading-5 text-white no-underline hover:text-white hover:underline"
                >
                    <Image
                        image={article.mainImage}
                        type="list"
                        alt={article.mainImage?.name || article.name}
                        className="rounded"
                    />
                </ArticleLink>

                <div className="mt-2 flex flex-col items-start gap-2">
                    {article.blogCategories.map((blogCategory) => (
                        <Fragment key={blogCategory.uuid}>
                            {!!blogCategory.parent && <Flag href={blogCategory.link}>{blogCategory.name}</Flag>}
                        </Fragment>
                    ))}

                    <ArticleLink
                        href={article.link}
                        className="block text-lg font-bold leading-5 text-white no-underline hover:text-white hover:underline"
                    >
                        {article.name}
                    </ArticleLink>

                    <div className="leading-5">{article.perex}</div>
                </div>
            </div>
        ))}
    </>
);
