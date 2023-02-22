import { Main } from './Main/Main';
import { Side } from './Side/Side';
import { SideSlider } from './SideSlider/SideSlider';
import { Icon } from 'components/Basic/Icon/Icon';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useBlogPreviewArticles } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { useBlogUrl } from 'connectors/blogCategory/BlogCategory';
import { ListedBlogArticleFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import NextLink from 'next/link';
import { useMemo, useState } from 'react';

const TEST_IDENTIFIER = 'blocks-blogpreview';

export const BlogPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const blogPreviewItems = useBlogPreviewArticles();
    const blogUrl = useBlogUrl();
    const { width } = useGetWindowSize();
    const [isBlogPreviewArticlesSideSliderVisible, setBlogPreviewArticlesSideSliderVisibility] = useState(false);
    const [blogMainItems, blogSideItems] = useMemo(() => {
        const updatedBlogMainItems: ListedBlogArticleFragmentApi[] = [];
        const updatedBlogSideItems: ListedBlogArticleFragmentApi[] = [];

        if (blogPreviewItems?.edges === undefined || blogPreviewItems.edges === null) {
            return [undefined, undefined];
        }

        for (let i = 0; i < blogPreviewItems.edges.length; i++) {
            const currentBlogPreviewItem = blogPreviewItems.edges[i];

            if (currentBlogPreviewItem?.node === undefined || currentBlogPreviewItem.node === null) {
                continue;
            }

            if (i >= 2) {
                updatedBlogSideItems.push(currentBlogPreviewItem.node);
            } else {
                updatedBlogMainItems.push(currentBlogPreviewItem.node);
            }
        }

        return [updatedBlogMainItems, updatedBlogSideItems];
    }, [blogPreviewItems?.edges]);

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
                    <NextLink href={blogUrl} passHref>
                        <a className="mb-2 flex items-center font-bold uppercase text-creamWhite no-underline hover:text-creamWhite hover:no-underline">
                            {t('View all')}
                            <Icon
                                iconType="icon"
                                icon="ArrowRight"
                                className="relative top-0 ml-2 text-xs text-creamWhite"
                            />
                        </a>
                    </NextLink>
                )}
            </div>

            <div className="flex flex-wrap">
                <div className="mb-8 flex flex-col lg:-ml-11 lg:flex-row vl:mb-0 vl:flex-1 xl:-ml-20">
                    {!!blogMainItems && <Main blogMainItems={blogMainItems} />}
                </div>
                {!!blogMainItems && (
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
