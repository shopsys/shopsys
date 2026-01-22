import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useMediaMin } from 'utils/ui/useMediaMin';

export type BlogPreviewProps = {
    blogArticles: TypeBlogArticleConnectionFragment['edges'] | undefined;
    blogUrl: string | null | undefined;
    fetchingArticles: boolean;
};

export const BlogPreview: FC<BlogPreviewProps> = ({ blogArticles, blogUrl, fetchingArticles }) => {
    const { t } = useTranslation();

    const blogItems = mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles);
    const blogMainItems = blogItems?.slice(0, 2);
    const blogSideItems = blogItems?.slice(2);

    const isDesktop = useMediaMin('vl');

    return (
        <Webline className="z-above relative">
            <div className="mb-5 flex items-center justify-between">
                <h2 className="h3 text-text-inverted">{t('Magazine')}</h2>

                {!!blogUrl && (
                    <ExtendedNextLink
                        aria-label={t('Go to all articles page', { ns: 'accessibility' })}
                        className="font-secondary text-text-inverted hover:text-text-inverted text-sm font-semibold tracking-wide no-underline hover:underline"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        {t('All articles')}
                    </ExtendedNextLink>
                )}
            </div>

            {fetchingArticles && <SkeletonModuleMagazine />}

            {!fetchingArticles && !!(blogMainItems || blogSideItems) && (
                <div
                    className={twJoin(
                        'vl:flex vl:justify-between vl:gap-16 hide-scrollbar grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain',
                        'auto-cols-[60%] md:auto-cols-[40%] lg:auto-cols-[30%]',
                    )}
                >
                    {isDesktop ? (
                        <>
                            {!!blogMainItems && <BlogPreviewMain articles={blogMainItems} />}
                            {!!blogSideItems && <BlogPreviewSide articles={blogSideItems} />}
                        </>
                    ) : (
                        !!blogItems && <BlogPreviewMain articles={blogItems} />
                    )}
                </div>
            )}
        </Webline>
    );
};
