import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ArticleLink } from './BlogPreviewElements';

type SideProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export const BlogPreviewSide: FC<SideProps> = ({ articles, isPlaceholder = false }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col gap-6 xl:w-85 xl:shrink-0">
            {articles.map((article) => (
                <ArticleLink
                    preventRedirectOnTextSelection
                    ariaLabel={t('Go to article page of {{ articleName }}', {
                        ns: 'accessibility',
                        articleName: article.name,
                    })}
                    className="group flex select-text snap-start flex-col gap-5 xl:flex-row"
                    draggable={false}
                    href={article.link}
                    key={article.uuid}
                    title={t('Blog article')}
                >
                    <div className="aspect-video w-full shrink-0 overflow-hidden rounded-xl xl:h-24 xl:w-36">
                        <Image
                            alt={article.mainImage?.name || article.name}
                            className="size-full object-cover transition-transform duration-300 ease-out group-hover:scale-105"
                            height={96}
                            src={article.mainImage?.url}
                            tid={TIDs.blog_preview_image}
                            width={144}
                        />
                    </div>

                    <div className="flex flex-col items-start gap-2">
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

                        <h3 className="h5 text-text-inverted group-hover:underline">
                            <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible:bg-orange-500 group-focus-visible:text-text-default!">
                                {article.name}
                            </span>
                        </h3>

                        <p className={twJoin('font-normal text-text-inverted', !isPlaceholder && 'hidden')}>
                            {article.perex}
                        </p>
                    </div>
                </ArticleLink>
            ))}
        </div>
    );
};
