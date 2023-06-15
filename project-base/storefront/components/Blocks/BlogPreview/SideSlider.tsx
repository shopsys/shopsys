import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { mediaQueries } from 'components/Theme/mediaQueries';
import { ListedBlogArticleFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import NextLink from 'next/link';
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
                {blogSideItems.map((blogSideItem, index) => {
                    const blogSideItemImage = getFirstImageOrNull(blogSideItem.images);

                    return (
                        <div
                            key={index}
                            className="keen-slider__slide flex flex-col"
                            data-testid={TEST_IDENTIFIER + index}
                        >
                            <div className="flex w-full">
                                <NextLink href={blogSideItem.link} passHref>
                                    <a className="relative mb-2 flex w-full">
                                        <Image
                                            image={blogSideItemImage}
                                            type="list"
                                            alt={blogSideItemImage?.name || blogSideItem.name}
                                            className="max-h-32 rounded"
                                        />
                                    </a>
                                </NextLink>
                            </div>
                            <div className="flex-1">
                                {blogSideItem.blogCategories.map((blogPreviewCategorie, index) => (
                                    <Fragment key={index}>
                                        {blogPreviewCategorie.parent !== null && (
                                            <Flag href={blogPreviewCategorie.link}>{blogPreviewCategorie.name}</Flag>
                                        )}
                                    </Fragment>
                                ))}

                                <NextLink href={blogSideItem.link} passHref>
                                    <a className="block text-lg font-bold leading-5 text-creamWhite no-underline hover:text-creamWhite">
                                        {blogSideItem.name}
                                    </a>
                                </NextLink>
                            </div>
                        </div>
                    );
                })}
            </div>
        </>
    );
};
