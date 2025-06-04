import { ArticleLink } from './BlogPreviewElements';
import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { twJoin } from 'tailwind-merge';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type SideProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export const BlogPreviewSide: FC<SideProps> = ({ articles, isPlaceholder = false }) => {
    const { formatDate } = useFormatDate();

    return (
        <div className="flex flex-col gap-6">
            {articles.map((article) => (
                <ArticleLink
                    key={article.uuid}
                    className="vl:flex-row focus-visible:bg-background-more/10 flex max-w-[410px] min-w-96 snap-start flex-col gap-5 rounded-xl no-underline hover:no-underline focus-visible:ring-0 focus-visible:ring-offset-0"
                    href={article.link}
                >
                    <Image
                        alt={article.mainImage?.name || article.name}
                        className="vl:h-24 vl:w-36 aspect-video rounded-xl object-cover"
                        height={220}
                        sizes="(max-width: 1023px) 0px, 144px"
                        src={article.mainImage?.url}
                        tid={TIDs.blog_preview_image}
                        width={320}
                    />

                    <div className="flex flex-col items-start gap-2">
                        <div className="flex flex-wrap items-center gap-2 whitespace-nowrap">
                            {isPlaceholder ? (
                                <>
                                    <Skeleton className="mr-6 h-5 w-20" />
                                    <Skeleton className="h-5 w-32" />
                                </>
                            ) : (
                                <>
                                    <ArticleDate
                                        className="mr-4"
                                        data-tid={TIDs.blog_article_publication_date}
                                        date={formatDate(article.publishDate, 'l')}
                                    />

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

                        <span className="h5 text-text-inverted">{article.name}</span>

                        <p className={twJoin('text-text-inverted font-normal', !isPlaceholder && 'hidden')}>
                            {article.perex}
                        </p>
                    </div>
                </ArticleLink>
            ))}
        </div>
    );
};
