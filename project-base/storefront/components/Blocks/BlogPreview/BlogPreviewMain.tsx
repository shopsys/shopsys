import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { Fragment } from 'react';
import Skeleton from 'react-loading-skeleton';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type MainProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export const BlogPreviewMain: FC<MainProps> = ({ articles, isPlaceholder = false }) => {
    const { formatDate } = useFormatDate();

    return (
        <>
            {articles.map((article) => (
                <ArticleLink
                    key={article.uuid}
                    className="flex flex-col gap-5 snap-start no-underline hover:no-underline max-w-80"
                    href={article.link}
                >
                    <Image
                        alt={article.mainImage?.name || article.name}
                        className="rounded-xl aspect-video object-cover"
                        height={220}
                        sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 35vw"
                        src={article.mainImage?.url}
                        tid={TIDs.blog_preview_image}
                        width={320}
                    />

                    <div className="flex flex-col items-start gap-2.5">
                        <div className="flex items-center gap-x-6 gap-y-1 flex-wrap">
                            {isPlaceholder ? (
                                <>
                                    <Skeleton className="w-20 h-5" />
                                    <Skeleton className="w-32 h-5" />
                                </>
                            ) : (
                                <>
                                    <span className="text-inputPlaceholder text-sm font-secondary font-semibold">
                                        {formatDate(article.publishDate, 'l')}
                                    </span>

                                    {article.blogCategories.map((blogPreviewCategory) => {
                                        if (!blogPreviewCategory.parent) {
                                            return null;
                                        }

                                        return (
                                            <Flag
                                                key={blogPreviewCategory.uuid}
                                                href={blogPreviewCategory.link}
                                                type="blog"
                                            >
                                                {blogPreviewCategory.name}
                                            </Flag>
                                        );
                                    })}
                                </>
                            )}
                        </div>

                        <h4 className="text-textInverted">{article.name}</h4>

                        <p className="text-textInverted font-normal">{article.perex}</p>
                    </div>
                </ArticleLink>
            ))}
        </>
    );
};
