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
import Main from './Main';
import Side from './Side';
import SideSlider from './SideSlider';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useBlogPreviewArticles } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { useBlogUrl } from 'connectors/blogCategory/BlogCategory';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import NextLink from 'next/link';
import { FC, useState } from 'react';

const BlogPreview: FC = () => {
    const testIdentifier = 'blocks-blogpreview';

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
        <BlogPreviewStyled data-testid={testIdentifier}>
            <BlogPreviewHeadingStyled>
                <BlogPreviewHeadingTitleStyled>{t('Shopsys magazine')}</BlogPreviewHeadingTitleStyled>
                {blogUrl !== undefined && (
                    <NextLink href={blogUrl} passHref>
                        <BlogPreviewHeadingLinkStyled>
                            <span>{t('View all')}</span>
                            <BlogPreviewHeadingLinkIconStyled iconType="icon" icon="ArrowRight" />
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

export default BlogPreview;
