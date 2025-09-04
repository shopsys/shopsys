import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'app/_components/Basic/Flag/Flag';
import { getFormatDate } from 'app/_utils/formatting/getFormatDate';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Image } from 'components/Basic/Image/Image';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.ssr';

type MainProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export async function BlogPreviewMain({ articles, isPlaceholder = false }: MainProps) {
    const { formatDate } = await getFormatDate();
    const t = await getTranslation();

    return (
        <>
            {articles.map((article) => (
                <div key={article.uuid} className="flex max-w-80 snap-start flex-col gap-5">
                    <ArticleLink href={article.link} tabIndex={-1} title={t('Article page')}>
                        <Image
                            alt={article.mainImage?.name || article.name}
                            className="vl:aspect-16/11 aspect-video size-full rounded-xl object-cover"
                            height={220}
                            sizes="(max-width: 600px) 52vw, (max-width: 768px) 35vw, (max-width: 1024px) 28vw, 320px"
                            src={article.mainImage?.url}
                            tid={TIDs.blog_preview_image}
                            width={320}
                        />
                    </ArticleLink>

                    <div className="flex flex-col items-start gap-2.5">
                        <div className="flex flex-wrap items-center gap-x-1.5 gap-y-2 whitespace-nowrap">
                            {isPlaceholder ? (
                                <>
                                    <Skeleton className="mr-6 h-5 w-20" />
                                    <Skeleton className="h-5 w-32" />
                                </>
                            ) : (
                                <>
                                    {/* TODO: add ArticleDate */}
                                    {/* <ArticleDate
                                        className="mr-3.5"
                                        date={article.publishDate}
                                        tid={TIDs.blog_article_publication_date}
                                    /> */}

                                    <span
                                        className="font-secondary text-input-placeholder-default mr-4 text-sm font-semibold"
                                        tid={TIDs.blog_article_publication_date}
                                    >
                                        {formatDate(article.publishDate, 'l')}
                                    </span>

                                    {article.blogCategories.map((blogPreviewCategory) => {
                                        if (!blogPreviewCategory.parent) {
                                            return null;
                                        }

                                        return (
                                            <Flag key={blogPreviewCategory.uuid} type="blog">
                                                {blogPreviewCategory.name}
                                            </Flag>
                                        );
                                    })}
                                </>
                            )}
                        </div>

                        <ArticleLink
                            className="h4 text-text-inverted"
                            href={article.link}
                            title={t('Blog article')}
                            ariaLabel={t('Go to article page of {{ articleName }}', {
                                ns: 'accessibility',
                                articleName: article.name,
                            })}
                        >
                            {article.name}
                        </ArticleLink>

                        <p className="text-text-inverted font-normal">{article.perex}</p>
                    </div>
                </div>
            ))}
        </>
    );
}
