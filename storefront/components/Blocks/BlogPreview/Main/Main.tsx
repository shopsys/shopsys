import {
    MainContentStyled,
    MainDescriptionStyled,
    MainImageLinkStyled,
    MainImageStyled,
    MainItemStyled,
    MainNameStyled,
} from './Main.style';
import Flag from 'components/Basic/Flag';
import Image from 'components/Basic/Image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import NextLink from 'next/link';
import { FC, Fragment, useState } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type MainProps = {
    blogMainItems: ListedBlogArticleType[];
};

const Main: FC<MainProps> = (props) => {
    const testIdentifier = 'blocks-blogpreview-main-';

    const { width } = useGetWindowSize();
    const [isOneMainArticle, setOnlyOneMainArticle] = useState(false);
    const visibleArticles = isOneMainArticle ? 1 : 2;

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setOnlyOneMainArticle(false),
        () => setOnlyOneMainArticle(true),
        () => setOnlyOneMainArticle(isElementVisible([{ min: 0, max: desktopFirstSizes.tablet }], width)),
    );

    return (
        <>
            {props.blogMainItems.map(
                (blogMainItem, index) =>
                    index < visibleArticles && (
                        <MainItemStyled key={index} data-testid={testIdentifier + index}>
                            <MainImageStyled>
                                <NextLink href={blogMainItem.link} passHref>
                                    <MainImageLinkStyled>
                                        <Image image={blogMainItem.image} type="list" alt="alt" />
                                    </MainImageLinkStyled>
                                </NextLink>
                            </MainImageStyled>
                            <MainContentStyled>
                                {blogMainItem.blogCategories.map((blogPreviewCategory, index) => (
                                    <Fragment key={index}>
                                        {blogPreviewCategory.parent !== null && (
                                            <Flag color="#cdb3ff" href={blogPreviewCategory.link}>
                                                {blogPreviewCategory.name}
                                            </Flag>
                                        )}
                                    </Fragment>
                                ))}
                                <NextLink href={blogMainItem.link} passHref>
                                    <MainNameStyled>{blogMainItem.name}</MainNameStyled>
                                </NextLink>
                                <MainDescriptionStyled>{blogMainItem.perex}</MainDescriptionStyled>
                            </MainContentStyled>
                        </MainItemStyled>
                    ),
            )}
        </>
    );
};

export default Main;
