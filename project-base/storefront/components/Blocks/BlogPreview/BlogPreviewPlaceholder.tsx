import { BlogPreviewProps } from './BlogPreview';
import { BlogPreviewMain } from './BlogPreviewMain';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeListedBlogArticleFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/ListedBlogArticleFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';

type BlogPreviewPlaceholderProps = Pick<BlogPreviewProps, 'blogArticles' | 'blogUrl'>;

export const BlogPreviewPlaceholder: FC<BlogPreviewPlaceholderProps> = ({ blogArticles, blogUrl }) => {
    const { t } = useTranslation();

    const blogItems = mapConnectionEdges<TypeListedBlogArticleFragment>(blogArticles);

    return (
        <Webline className="z-above relative">
            <div className="mb-5 flex items-center justify-between">
                <span className="h3 text-text-inverted">{t('Magazine')}</span>

                {!!blogUrl && (
                    <ExtendedNextLink
                        className="font-secondary text-text-inverted hover:text-text-inverted text-sm font-semibold tracking-wide no-underline hover:underline"
                        href={blogUrl}
                        type="blogCategory"
                    >
                        {t('All articles')}
                    </ExtendedNextLink>
                )}
            </div>

            <div
                className={twJoin(
                    'vl:flex vl:justify-between vl:gap-16 hide-scrollbar grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain',
                    'auto-cols-[60%] md:auto-cols-[40%] lg:auto-cols-[30%]',
                )}
            >
                {!!blogItems && <BlogPreviewMain isPlaceholder articles={blogItems} />}
            </div>
        </Webline>
    );
};
