import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import Skeleton from 'react-loading-skeleton';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type SideProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export const BlogPreviewSide: FC<SideProps> = ({ articles, isPlaceholder = false }) => {
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

                        <h5 className="text-textInverted">{article.name}</h5>

                        {isPlaceholder && <p className="text-textInverted font-normal">{article.perex}</p>}
                    </div>
                </ArticleLink>
            ))}
        </div>
    );
};
