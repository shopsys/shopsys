import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';

export type BlogPreviewProps = {
    blogArticles: TypeBlogArticleConnectionFragment['edges'] | undefined;
    blogName: string | null | undefined;
    blogUrl: string | null | undefined;
    fetchingArticles: boolean;
};

export const BlogPreview: FC<BlogPreviewProps> = ({ blogArticles, blogName, blogUrl, fetchingArticles }) => {
    const { t } = useTranslation();

    const blogItems = mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles);
    const blogMainItems = blogItems?.slice(0, 3);
    const blogSideItems = blogItems?.slice(3);

    const isDesktop = useMediaMin('xl');

    return (
        <Webline className="relative z-above">
            <div className="mb-5 flex items-center justify-between">
                <h2 className="h3 text-text-inverted">{blogName || t('Magazine')}</h2>

                {!!blogUrl && (
                    <ExtendedNextLink
                        aria-label={t('Go to all articles page', { ns: 'accessibility' })}
                        className="group flex items-center gap-2 font-secondary font-semibold text-sm text-text-inverted tracking-wide no-underline hover:text-text-inverted"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        {t('All articles')}
                        <ArrowSecondaryIcon className="size-3 -rotate-90 transition-transform group-hover:translate-x-0.5" />
                    </ExtendedNextLink>
                )}
            </div>

            {fetchingArticles && <SkeletonModuleMagazine />}

            {!fetchingArticles && !!(blogMainItems || blogSideItems) && (
                <div
                    className={twJoin(
                        'hide-scrollbar grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain xl:flex xl:justify-between xl:gap-10',
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
