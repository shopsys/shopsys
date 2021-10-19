import { FC, Fragment } from 'react';
import { SideContentStyled, SideImageLinkStyled, SideImageStyled, SideItemStyled, SideNameStyled } from './Side.style';
import { BlogPreviewType } from 'connectors/blogPreview/blogPreview';
import Flag from 'components/Basic/Flag';
import Image from 'components/Basic/Image';

type SideProps = {
    blogSideItems: BlogPreviewType[];
};

const Side: FC<SideProps> = (props) => {
    return (
        <>
            {props.blogSideItems.map((blogSideItem, index) => (
                <SideItemStyled key={index}>
                    <SideImageStyled>
                        <SideImageLinkStyled href={blogSideItem.link}>
                            <Image image={blogSideItem.image} alt="alt" />
                        </SideImageLinkStyled>
                    </SideImageStyled>
                    <SideContentStyled>
                        {blogSideItem.blogCategories.map((blogPreviewCategorie, index) => (
                            <Fragment key={index}>
                                {blogPreviewCategorie.parent !== null && (
                                    <Flag color="#cdb3ff" href={blogPreviewCategorie.link}>
                                        {blogPreviewCategorie.name}
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
