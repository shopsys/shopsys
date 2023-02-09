import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { theme } from 'components/Theme/main';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import NextLink from 'next/link';
import { Fragment } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type SideSliderProps = {
    blogSideItems: ListedBlogArticleType[];
};

const TEST_IDENTIFIER = 'blocks-blogpreview-sideslider-';

export const SideSlider: FC<SideSliderProps> = ({ blogSideItems }) => {
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
                {blogSideItems.map((blogSideItem, index) => (
                    <div key={index} className="keen-slider__slide flex flex-col" data-testid={TEST_IDENTIFIER + index}>
                        <div className="flex w-full">
                            <NextLink href={blogSideItem.link} passHref>
                                <a className="relative mb-2 flex w-full">
                                    <Image
                                        image={getFirstImageOrNull(blogSideItem.images)}
                                        type="list"
                                        alt="alt"
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
                ))}
            </div>
        </>
    );
};
