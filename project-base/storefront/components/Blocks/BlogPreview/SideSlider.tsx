import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { mediaQueries } from 'components/Theme/mediaQueries';
import { ListedBlogArticleFragmentApi } from 'graphql/generated';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { Fragment } from 'react';

type SideSliderProps = {
    blogSideItems: ListedBlogArticleFragmentApi[];
};

const TEST_IDENTIFIER = 'blocks-blogpreview-sideslider-';

export const SideSlider: FC<SideSliderProps> = ({ blogSideItems }) => {
    const [sliderRef] = useKeenSlider<HTMLDivElement>({
        breakpoints: {
            [mediaQueries.queryNotLargeDesktop]: {
                slidesPerView: 3.2,
                spacing: 24,
            },
            [mediaQueries.queryTablet]: {
                slidesPerView: 2.2,
                spacing: 24,
            },
            [mediaQueries.queryMobileXs]: {
                slidesPerView: 1.2,
                spacing: 24,
            },
        },
    });
    return (
        <>
            <div ref={sliderRef} className="keen-slider">
                {blogSideItems.map((blogSideItem, index) => (
                    <div key={index} className="keen-slider__slide flex flex-col" data-testid={TEST_IDENTIFIER + index}>
                        <div className="flex w-full">
                            <ExtendedNextLink type="blogArticle" href={blogSideItem.link} passHref>
                                <a className="relative mb-2 flex w-full">
                                    <Image
                                        image={blogSideItem.mainImage}
                                        type="list"
                                        alt={blogSideItem.mainImage?.name || blogSideItem.name}
                                        className="max-h-32 rounded"
                                    />
                                </a>
                            </ExtendedNextLink>
                        </div>
                        <div className="flex-1">
                            {blogSideItem.blogCategories.map((blogPreviewCategorie, index) => (
                                <Fragment key={index}>
                                    {blogPreviewCategorie.parent !== null && (
                                        <Flag href={blogPreviewCategorie.link}>{blogPreviewCategorie.name}</Flag>
                                    )}
                                </Fragment>
                            ))}

                            <ExtendedNextLink type="blogArticle" href={blogSideItem.link} passHref>
                                <a className="block text-lg font-bold leading-5 text-creamWhite no-underline hover:text-creamWhite">
                                    {blogSideItem.name}
                                </a>
                            </ExtendedNextLink>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
};
