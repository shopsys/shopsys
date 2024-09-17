import { BlogPreviewProps } from './BlogPreview';
import { BlogPreviewMain } from './BlogPreviewMain';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { twJoin } from 'tailwind-merge';
import { mapConnectionEdges } from 'utils/mappers/connection';

type BlogPreviewPlaceholderProps = Pick<BlogPreviewProps, 'blogArticles' | 'blogUrl'>;

export const BlogPreviewPlaceholder: FC<BlogPreviewPlaceholderProps> = ({ blogArticles, blogUrl }) => {
    const { t } = useTranslation();

    const blogItems = useMemo(() => mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles), [blogArticles]);

    return (
        <div className="w-full max-w-7xl mx-auto px-5 z-above relative">
            <div className="mb-5 flex items-center justify-between">
                <h3 className="text-textInverted">{t('Magazine')}</h3>

                {!!blogUrl && (
                    <ExtendedNextLink
                        className="no-underline text-textInverted text-sm font-secondary font-semibold tracking-wide hover:underline hover:text-textInverted"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        {t('All articles')}
                    </ExtendedNextLink>
                )}
            </div>

            <div
                className={twJoin(
                    'grid gap-5 grid-flow-col overflow-x-auto overscroll-x-contain snap-x snap-mandatory vl:flex vl:justify-between vl:gap-16',
                    'auto-cols-[60%] md:auto-cols-[40%] lg:auto-cols-[30%]',
                    "[-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden",
                )}
            >
                {!!blogItems && <BlogPreviewMain isPlaceholder articles={blogItems} />}
            </div>
        </div>
    );
};
