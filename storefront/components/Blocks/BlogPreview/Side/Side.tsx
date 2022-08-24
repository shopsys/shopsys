import { SideContentStyled, SideImageLinkStyled, SideImageStyled, SideItemStyled, SideNameStyled } from './Side.style';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type SideProps = {
    blogSideItems: ListedBlogArticleType[];
};

export const Side: FC<SideProps> = (props) => {
    const testIdentifier = 'blocks-blogpreview-side-';

    return (
        <>
            {props.blogSideItems.map((blogSideItem, index) => (
                <SideItemStyled key={index} data-testid={testIdentifier + index}>
                    <SideImageStyled>
                        <NextLink href={blogSideItem.link} passHref>
                            <SideImageLinkStyled>
                                <Image image={blogSideItem.image} type="listAside" alt="alt" />
                            </SideImageLinkStyled>
                        </NextLink>
                    </SideImageStyled>
                    <SideContentStyled>
                        {blogSideItem.blogCategories.map((blogPreviewCategory, index) => (
                            <Fragment key={index}>
                                {blogPreviewCategory.parent !== null && (
                                    <Flag color="#cdb3ff" href={blogPreviewCategory.link}>
                                        {blogPreviewCategory.name}
                                    </Flag>
                                )}
                            </Fragment>
                        ))}
                        <NextLink href={blogSideItem.link} passHref>
                            <SideNameStyled>{blogSideItem.name}</SideNameStyled>
                        </NextLink>
                    </SideContentStyled>
                </SideItemStyled>
            ))}
        </>
    );
};
