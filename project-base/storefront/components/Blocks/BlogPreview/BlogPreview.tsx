import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Main } from './Main';
import { Side } from './Side';
import { SideSlider } from './SideSlider';
import { Icon } from 'components/Basic/Icon/Icon';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { ListedBlogArticleFragmentApi, useBlogArticlesQueryApi, useBlogUrlQueryApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useMemo, useState } from 'react';

export const BLOG_PREVIEW_VARIABLES = { first: 6, onlyHomepageArticles: true };
const TEST_IDENTIFIER = 'blocks-blogpreview';

export const BlogPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const [{ data: blogPreviewData }] = useQueryError(useBlogArticlesQueryApi({ variables: BLOG_PREVIEW_VARIABLES }));
    const [{ data: blogUrlData }] = useQueryError(useBlogUrlQueryApi());
    const blogUrl = blogUrlData?.blogCategories[0].link;
    const { width } = useGetWindowSize();
    const [isBlogPreviewArticlesSideSliderVisible, setBlogPreviewArticlesSideSliderVisibility] = useState(false);
    const blogMainItems = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(blogPreviewData?.blogArticles.edges?.slice(0, 2)),
        [blogPreviewData?.blogArticles.edges],
    );
    const blogSideItems = useMemo(
        () => mapConnectionEdges<ListedBlogArticleFragmentApi>(blogPreviewData?.blogArticles.edges?.slice(2)),
        [blogPreviewData?.blogArticles.edges],
    );

    useResizeWidthEffect(
        width,
        desktopFirstSizes.notLargeDesktop,
        () => setBlogPreviewArticlesSideSliderVisibility(false),
        () => setBlogPreviewArticlesSideSliderVisibility(true),
        () =>
            setBlogPreviewArticlesSideSliderVisibility(
                isElementVisible([{ min: 0, max: desktopFirstSizes.notLargeDesktop }], width),
            ),
    );

    return (
        <div className="pt-12 pb-10 vl:pb-16" data-testid={TEST_IDENTIFIER}>
            <div className="mb-5 flex flex-wrap items-baseline">
                <h2 className="mr-8 mb-2 transform-none text-3xl font-bold leading-9 text-creamWhite">
                    {t('Shopsys magazine')}
                </h2>
                {blogUrl !== undefined && (
                    <ExtendedNextLink type="blogCategory" href={blogUrl} passHref>
                        <a className="mb-2 flex items-center font-bold uppercase text-creamWhite no-underline hover:text-creamWhite hover:no-underline">
                            {t('View all')}
                            <Icon
                                iconType="icon"
                                icon="ArrowRight"
                                className="relative top-0 ml-2 text-xs text-creamWhite"
                            />
                        </a>
                    </ExtendedNextLink>
                )}
            </div>

            <div className="flex flex-wrap">
                <div className="mb-8 flex flex-col lg:-ml-11 lg:flex-row vl:mb-0 vl:flex-1 xl:-ml-20">
                    {!!blogMainItems && <Main blogMainItems={blogMainItems} />}
                </div>
                {!!blogSideItems && (
                    <div className="flex-col overflow-hidden vl:ml-12 vl:flex xl:ml-24">
                        {isBlogPreviewArticlesSideSliderVisible ? (
                            <SideSlider blogSideItems={blogSideItems} />
                        ) : (
                            <Side blogSideItems={blogSideItems} />
                        )}
                    </div>
                )}
            </div>
        </div>
    );
};
