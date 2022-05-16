import {
    SideSliderContentStyled,
    SideSliderImageLinkStyled,
    SideSliderImageStyled,
    SideSliderItemStyled,
    SideSliderNameStyled,
} from './SideSlider.style';
import Flag from 'components/Basic/Flag';
import Image from 'components/Basic/Image';
import { theme } from 'components/Theme/main';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { FC, Fragment } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type SideSliderProps = {
    blogSideItems: ListedBlogArticleType[];
};

const SideSlider: FC<SideSliderProps> = (props) => {
    const testIdentifier = 'blocks-blogpreview-sideslider-';

    const [sliderRef] = useKeenSlider<HTMLDivElement>({
        breakpoints: {
            [theme.mediaQueries.queryNotLargeDesktop]: {
                slidesPerView: 3.2,
                spacing: 24,
            },
            [theme.mediaQueries.queryTablet]: {
                slidesPerView: 2.2,
                spacing: 24,
            },
            [theme.mediaQueries.queryMobileXs]: {
                slidesPerView: 1.2,
                spacing: 24,
            },
        },
    });
    return (
        <>
            <div ref={sliderRef} className="keen-slider">
                {props.blogSideItems.map((blogSideItem, index) => (
                    <SideSliderItemStyled
                        key={index}
                        className="keen-slider__slide"
                        data-testid={testIdentifier + index}
                    >
                        <SideSliderImageStyled>
                            <SideSliderImageLinkStyled href={blogSideItem.link}>
                                <Image image={blogSideItem.image} type="list" alt="alt" />
                            </SideSliderImageLinkStyled>
                        </SideSliderImageStyled>
                        <SideSliderContentStyled>
                            {blogSideItem.blogCategories.map((blogPreviewCategorie, index) => (
                                <Fragment key={index}>
                                    {blogPreviewCategorie.parent !== null && (
                                        <Flag color="#cdb3ff" href={blogPreviewCategorie.link}>
                                            {blogPreviewCategorie.name}
                                        </Flag>
                                    )}
                                </Fragment>
                            ))}

                            <SideSliderNameStyled href={blogSideItem.link}>{blogSideItem.name}</SideSliderNameStyled>
                        </SideSliderContentStyled>
                    </SideSliderItemStyled>
                ))}
            </div>
        </>
    );
};

export default SideSlider;
