import { FC, Fragment, useState } from 'react';
import {
    MainContentStyled,
    MainDescriptionStyled,
    MainImageLinkStyled,
    MainImageStyled,
    MainItemStyled,
    MainNameStyled,
} from './Main.style';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import Flag from 'components/Basic/Flag';
import Image from 'components/Basic/Image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { ListedBlogArticleType } from 'types/blogArticle';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';

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
                                <MainImageLinkStyled href={blogMainItem.link}>
                                    <Image image={blogMainItem.image} type="list" alt="alt" />
                                </MainImageLinkStyled>
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

                                <MainNameStyled href={blogMainItem.link}>{blogMainItem.name}</MainNameStyled>
                                <MainDescriptionStyled>{blogMainItem.perex}</MainDescriptionStyled>
                            </MainContentStyled>
                        </MainItemStyled>
                    ),
            )}
        </>
    );
};

export default Main;
