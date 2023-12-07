import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { ListedBlogArticleFragmentApi } from 'graphql/generated';
import { Fragment } from 'react';

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
                    className="block text-lg font-bold leading-5 text-white no-underline hover:text-white hover:underline"
                    href={article.link}
                >
                    <Image
                        alt={article.mainImage?.name || article.name}
                        className="mx-auto rounded"
                        height={220}
                        sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 35vw"
                        src={article.mainImage?.url}
                        width={700}
                    />
                </ArticleLink>

                <div className="mt-2 flex flex-col items-start gap-2">
                    {article.blogCategories.map((blogCategory) => (
                        <Fragment key={blogCategory.uuid}>
                            {!!blogCategory.parent && <Flag href={blogCategory.link}>{blogCategory.name}</Flag>}
                        </Fragment>
                    ))}

                    <ArticleLink
                        className="block text-lg font-bold leading-5 text-white no-underline hover:text-white hover:underline"
                        href={article.link}
                    >
                        {article.name}
                    </ArticleLink>

                    <div className="leading-5">{article.perex}</div>
                </div>
            </div>
        ))}
    </>
);
