import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ArticleLink } from './BlogPreviewElements';

type MainProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export const BlogPreviewMain: FC<MainProps> = ({ articles, isPlaceholder = false }) => {
    const { t } = useTranslation();

    return (
        <>
            {articles.map((article) => (
                <ArticleLink
                    preventRedirectOnTextSelection
                    ariaLabel={t('Go to article page of {{ articleName }}', {
                        ns: 'accessibility',
                        articleName: article.name,
                    })}
                    className="group flex max-w-80 select-text snap-start flex-col gap-5 xl:min-w-0 xl:flex-1"
                    draggable={false}
                    href={article.link}
                    key={article.uuid}
                    title={t('Blog article')}
                >
                    <div className="overflow-hidden rounded-xl">
                        <Image
                            alt={article.mainImage?.name || article.name}
                            className="aspect-video size-full object-cover transition-transform duration-300 ease-out group-hover:scale-105 xl:aspect-16/11"
                            height={220}
                            sizes="(max-width: 600px) 52vw, (max-width: 768px) 35vw, (max-width: 1024px) 28vw, 320px"
                            src={article.mainImage?.url}
                            tid={TIDs.blog_preview_image}
                            width={320}
                        />
                    </div>

                    <div className="flex flex-col items-start gap-2.5">
                        <div className="flex flex-wrap items-center gap-x-1.5 gap-y-2 whitespace-nowrap">
                            {isPlaceholder ? (
                                <>
                                    <Skeleton className="mr-6 h-5 w-20" />
                                    <Skeleton className="h-5 w-32" />
                                </>
                            ) : (
                                <>
                                    <ArticleDate
                                        className="mr-3.5 text-secondary-300!"
                                        date={article.publishDate}
                                        tid={TIDs.blog_article_publication_date}
                                    />

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

                        <h3 className="h4 text-text-inverted group-hover:underline">
                            <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible:bg-orange-500 group-focus-visible:text-text-default!">
                                {article.name}
                            </span>
                        </h3>

                        <p className="font-normal text-text-inverted">{article.perex}</p>
                    </div>
                </ArticleLink>
            ))}
        </>
    );
};
