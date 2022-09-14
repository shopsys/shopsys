import {
    BlogPreviewArticlesMainStyled,
    BlogPreviewArticlesSideStyled,
    BlogPreviewArticlesStyled,
    BlogPreviewHeadingLinkIconStyled,
    BlogPreviewHeadingLinkStyled,
    BlogPreviewHeadingStyled,
    BlogPreviewHeadingTitleStyled,
    BlogPreviewStyled,
} from './BlogPreview.style';
import { Main } from './Main/Main';
import { Side } from './Side/Side';
import { SideSlider } from './SideSlider/SideSlider';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useBlogPreviewArticles } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { useBlogUrl } from 'connectors/blogCategory/BlogCategory';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import NextLink from 'next/link';
import { FC, useState } from 'react';

const TEST_IDENTIFIER = 'blocks-blogpreview';

export const BlogPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const blogPreviewItems = useBlogPreviewArticles();
    const blogUrl = useBlogUrl();
    const { width } = useGetWindowSize();
    const [isBlogPreviewArticlesSideSliderVisible, setBlogPreviewArticlesSideSliderVisibility] = useState(false);
    const blogMainItems = blogPreviewItems.slice(0, 2);
    const blogSideItems = blogPreviewItems.slice(2);

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
        <BlogPreviewStyled data-testid={TEST_IDENTIFIER}>
            <BlogPreviewHeadingStyled>
                <BlogPreviewHeadingTitleStyled>{t('Shopsys magazine')}</BlogPreviewHeadingTitleStyled>
                {blogUrl !== undefined && (
                    <NextLink href={blogUrl} passHref>
                        <BlogPreviewHeadingLinkStyled>
                            <span>{t('View all')}</span>
                            <BlogPreviewHeadingLinkIconStyled alt="" iconType="icon" icon="ArrowRight" />
                        </BlogPreviewHeadingLinkStyled>
                    </NextLink>
                )}
            </BlogPreviewHeadingStyled>

            <BlogPreviewArticlesStyled>
                <BlogPreviewArticlesMainStyled>
                    <Main blogMainItems={blogMainItems} />
                </BlogPreviewArticlesMainStyled>
                <BlogPreviewArticlesSideStyled>
                    {isBlogPreviewArticlesSideSliderVisible ? (
                        <SideSlider blogSideItems={blogSideItems} />
                    ) : (
                        <Side blogSideItems={blogSideItems} />
                    )}
                </BlogPreviewArticlesSideStyled>
            </BlogPreviewArticlesStyled>
        </BlogPreviewStyled>
    );
};
