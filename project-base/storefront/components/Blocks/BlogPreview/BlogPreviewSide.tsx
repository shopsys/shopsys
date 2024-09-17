import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type SideProps = {
    articles: TypeListedBlogArticleFragment[];
};

export const BlogPreviewSide: FC<SideProps> = ({ articles }) => {
    const { formatDate } = useFormatDate();

    return (
        <div className="flex flex-col gap-6 flex-1">
            {articles.map((article) => (
                <ArticleLink
                    key={article.uuid}
                    className="flex flex-col vl:flex-row gap-5 snap-start no-underline hover:no-underline"
                    href={article.link}
                >
                    <Image
                        alt={article.mainImage?.name || article.name}
                        className="rounded-xl aspect-video object-cover vl:max-h-24 vl:max-w-36"
                        height={220}
                        sizes="(max-width: 600px) 90vw, (max-width: 1024px) 40vw, 10vw"
                        src={article.mainImage?.url}
                        tid={TIDs.blog_preview_image}
                        width={320}
                    />

                    <div className="flex flex-col items-start gap-2">
                        <div className="flex items-center gap-6 whitespace-nowrap">
                            <span className="text-inputPlaceholder text-sm font-secondary font-semibold">
                                {formatDate(article.publishDate, 'l')}
                            </span>
                            {article.blogCategories.map((blogPreviewCategorie) => (
                                <Fragment key={blogPreviewCategorie.uuid}>
                                    {blogPreviewCategorie.parent && (
                                        <Flag href={blogPreviewCategorie.link} type="blog">
                                            {blogPreviewCategorie.name}
                                        </Flag>
                                    )}
                                </Fragment>
                            ))}
                        </div>

                        <h5 className="text-textInverted">{article.name}</h5>
                    </div>
                </ArticleLink>
            ))}
        </div>
    );
};
