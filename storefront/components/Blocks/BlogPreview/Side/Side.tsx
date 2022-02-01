import { FC, Fragment } from 'react';
import { SideContentStyled, SideImageLinkStyled, SideImageStyled, SideItemStyled, SideNameStyled } from './Side.style';
import Flag from 'components/Basic/Flag';
import Image from 'components/Basic/Image';
import { ListedBlogArticleType } from 'types/blogArticle';

type SideProps = {
    blogSideItems: ListedBlogArticleType[];
};

const Side: FC<SideProps> = (props) => {
    const testIdentifier = 'blocks-blogpreview-side-';

    return (
        <>
            {props.blogSideItems.map((blogSideItem, index) => (
                <SideItemStyled key={index} data-testid={testIdentifier + index}>
                    <SideImageStyled>
                        <SideImageLinkStyled href={blogSideItem.link}>
                            <Image image={blogSideItem.image} alt="alt" />
                        </SideImageLinkStyled>
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

                        <SideNameStyled href={blogSideItem.link}>{blogSideItem.name}</SideNameStyled>
                    </SideContentStyled>
                </SideItemStyled>
            ))}
        </>
    );
};

export default Side;
