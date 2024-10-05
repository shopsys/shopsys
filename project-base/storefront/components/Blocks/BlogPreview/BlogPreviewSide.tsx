import { ArticleLink } from './BlogPreviewElements';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import Skeleton from 'react-loading-skeleton';
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
                    className="flex min-w-96 max-w-[410px] snap-start flex-col gap-5 no-underline hover:no-underline vl:flex-row"
                    href={article.link}
                >
                    <Image
                        alt={article.mainImage?.name || article.name}
                        className="aspect-video rounded-xl object-cover vl:h-24 vl:w-36"
                        height={220}
                        sizes="(max-width: 600px) 90vw, (max-width: 1024px) 40vw, 10vw"
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
                                    <span className="mr-4 font-secondary text-sm font-semibold text-inputPlaceholder">
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

                        <p className={twJoin('font-normal text-textInverted', !isPlaceholder && 'hidden')}>
                            {article.perex}
                        </p>
                    </div>
                </ArticleLink>
            ))}
        </div>
    );
};
