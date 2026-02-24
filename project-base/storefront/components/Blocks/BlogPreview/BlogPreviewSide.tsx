import { ArticleLink } from './BlogPreviewElements';
import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type SideProps = {
    articles: TypeListedBlogArticleFragment[];
    isPlaceholder?: boolean;
};

export const BlogPreviewSide: FC<SideProps> = ({ articles, isPlaceholder = false }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col gap-6">
            {articles.map((article) => (
                <div key={article.uuid} className="vl:flex-row flex max-w-[410px] min-w-96 snap-start flex-col gap-5">
                    <ArticleLink href={article.link} tabIndex={-1} title={t('Blog article')}>
                        <div className="vl:h-24 vl:w-36 aspect-video w-full shrink-0 overflow-hidden rounded-xl">
                            <Image
                                alt={article.mainImage?.name || article.name}
                                className="size-full object-cover"
                                height={220}
                                sizes="(max-width: 1023px) 0px, 144px"
                                src={article.mainImage?.url}
                                tid={TIDs.blog_preview_image}
                                width={320}
                            />
                        </div>
                    </ArticleLink>

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
                                        className="mr-3.5"
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

                        <ArticleLink
                            className="h5 text-text-inverted"
                            href={article.link}
                            title={t('Blog article')}
                            ariaLabel={t('Go to article page of {{ articleName }}', {
                                ns: 'accessibility',
                                articleName: article.name,
                            })}
                        >
                            {article.name}
                        </ArticleLink>

                        <p className={twJoin('text-text-inverted font-normal', !isPlaceholder && 'hidden')}>
                            {article.perex}
                        </p>
                    </div>
                </div>
            ))}
        </div>
    );
};
