import { BlogPreviewMain } from './BlogPreviewMain';
import { BlogPreviewSide } from './BlogPreviewSide';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowRightIcon } from 'components/Basic/Icon/ArrowRightIcon';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

export type BlogPreviewProps = {
    blogArticles: TypeBlogArticleConnectionFragment['edges'] | undefined;
    blogUrl: string | null | undefined;
    fetchingArticles: boolean;
};

export const BlogPreview: FC<BlogPreviewProps> = ({ blogArticles, blogUrl, fetchingArticles }) => {
    const { t } = useTranslation();

    const blogMainItems = useMemo(
        () => mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles?.slice(0, 2)),
        [blogArticles],
    );
    const blogSideItems = useMemo(
        () => mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles?.slice(2)),
        [blogArticles],
    );

    return (
        <div className="py-10 vl:py-16 vl:pb-20">
            <div className="mb-5 flex flex-wrap items-baseline">
                <h2 className="mr-8 mb-2 transform-none text-3xl font-bold leading-9">{t('Shopsys magazine')}</h2>

                {!!blogUrl && (
                    <ExtendedNextLink
                        className="mb-2 flex items-center font-bold uppercase no-underline hover:no-underline"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        <>
                            {t('View all')}
                            <ArrowRightIcon className="relative top-0 ml-2 text-xs" />
                        </>
                    </ExtendedNextLink>
                )}
            </div>

            {fetchingArticles && <SkeletonModuleMagazine />}

            {!fetchingArticles && !!(blogMainItems || blogSideItems) && (
                <div className="flex flex-col gap-16 vl:flex-row  vl:justify-between">
                    {!!blogMainItems && (
                        <div className="flex flex-1 gap-6">
                            <BlogPreviewMain articles={blogMainItems} />
                        </div>
                    )}

                    {!!blogSideItems && <BlogPreviewSide articles={blogSideItems} />}
                </div>
            )}
        </div>
    );
};
