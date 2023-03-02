import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import NextLink from 'next/link';
import { Fragment, useState } from 'react';
import { ListedBlogArticleType } from 'types/blogArticle';

type MainProps = {
    blogMainItems: ListedBlogArticleType[];
};

const TEST_IDENTIFIER = 'blocks-blogpreview-main-';

export const Main: FC<MainProps> = ({ blogMainItems }) => {
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
            {blogMainItems.map(
                (blogMainItem, index) =>
                    index < visibleArticles && (
                        <div
                            className="flex w-full flex-col lg:w-1/2 lg:pl-11 vl:mb-3 vl:w-full xl:pl-20"
                            key={index}
                            data-testid={TEST_IDENTIFIER + index}
                        >
                            <div className="flex w-full max-w-xs">
                                <NextLink href={blogMainItem.link} passHref>
                                    <a className="relative mb-3 flex w-full">
                                        <Image
                                            image={blogMainItem.image}
                                            type="list"
                                            alt="alt"
                                            className="max-h-44 rounded"
                                        />
                                    </a>
                                </NextLink>
                            </div>
                            <div className="flex-1">
                                {blogMainItem.blogCategories.map((blogPreviewCategory, index) => (
                                    <Fragment key={index}>
                                        {blogPreviewCategory.parent !== null && (
                                            <Flag href={blogPreviewCategory.link}>{blogPreviewCategory.name}</Flag>
                                        )}
                                    </Fragment>
                                ))}
                                <NextLink href={blogMainItem.link} passHref>
                                    <a className="mb-2 block text-lg font-bold leading-5 text-white no-underline hover:text-white hover:no-underline">
                                        {blogMainItem.name}
                                    </a>
                                </NextLink>
                                <div className="leading-5 text-white">{blogMainItem.perex}</div>
                            </div>
                        </div>
                    ),
            )}
        </>
    );
};
