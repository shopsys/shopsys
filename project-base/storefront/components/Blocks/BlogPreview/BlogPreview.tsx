import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { twJoin } from 'tailwind-merge';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { useMediaMin } from 'utils/ui/useMediaMin';

export type BlogPreviewProps = {
    blogArticles: TypeBlogArticleConnectionFragment['edges'] | undefined;
    blogUrl: string | null | undefined;
    fetchingArticles: boolean;
};

export const BlogPreview: FC<BlogPreviewProps> = ({ blogArticles, blogUrl, fetchingArticles }) => {
    const { t } = useTranslation();

    const blogItems = useMemo(() => mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles), [blogArticles]);
    const blogMainItems = blogItems?.slice(0, 2);
    const blogSideItems = blogItems?.slice(2);

    const isDesktop = useMediaMin('vl');

    return (
        <div className="relative z-above mx-auto w-full max-w-7xl px-5">
            <div className="mb-5 flex items-center justify-between">
                <h3 className="text-textInverted">{t('Magazine')}</h3>

                {!!blogUrl && (
                    <ExtendedNextLink
                        className="font-secondary text-sm font-semibold tracking-wide text-textInverted no-underline hover:text-textInverted hover:underline"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        <>{t('All articles')}</>
                    </ExtendedNextLink>
                )}
            </div>

            {fetchingArticles && <SkeletonModuleMagazine />}

            {!fetchingArticles && !!(blogMainItems || blogSideItems) && (
                <div
                    className={twJoin(
                        'grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain vl:flex vl:justify-between vl:gap-16',
                        'auto-cols-[60%] md:auto-cols-[40%] lg:auto-cols-[30%]',
                        "[-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden",
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
        </div>
    );
};
