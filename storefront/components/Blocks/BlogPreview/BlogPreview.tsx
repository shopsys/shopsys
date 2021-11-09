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
import { FC, useState } from 'react';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { getBlogPreviewArticles } from 'connectors/blogPreview/blogPreview';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import Main from './Main';
import Side from './Side';
import SideSlider from './SideSlider';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const BlogPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const blogPreviewItems = getBlogPreviewArticles();
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
        <BlogPreviewStyled>
            <BlogPreviewHeadingStyled>
                <BlogPreviewHeadingTitleStyled>{t('Shopsys magazine')}</BlogPreviewHeadingTitleStyled>
                <BlogPreviewHeadingLinkStyled href="#">
                    <span>{t('View all')}</span>
                    <BlogPreviewHeadingLinkIconStyled iconType="icon" icon="ArrowRight" />
                </BlogPreviewHeadingLinkStyled>
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

export default BlogPreview;
