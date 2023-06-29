import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { ListedBlogArticleFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { Fragment } from 'react';

type SideProps = {
    blogSideItems: ListedBlogArticleFragmentApi[];
};

const TEST_IDENTIFIER = 'blocks-blogpreview-side-';

export const Side: FC<SideProps> = ({ blogSideItems }) => {
    return (
        <>
            {blogSideItems.map((blogSideItem, index) => {
                const blogSideItemImage = getFirstImageOrNull(blogSideItem.images);

                return (
                    <div className="mb-3 flex w-full flex-row" key={index} data-testid={TEST_IDENTIFIER + index}>
                        <div className="flex w-36">
                            <ExtendedNextLink type="blogArticle" href={blogSideItem.link} passHref>
                                <a className="relative flex w-full">
                                    <Image
                                        image={blogSideItemImage}
                                        type="listAside"
                                        alt={blogSideItemImage?.name || blogSideItem.name}
                                        className="max-h-20 rounded"
                                    />
                                </a>
                            </ExtendedNextLink>
                        </div>
                        <div className="ml-5 flex-1">
                            {blogSideItem.blogCategories.map((blogPreviewCategory, index) => (
                                <Fragment key={index}>
                                    {blogPreviewCategory.parent !== null && (
                                        <Flag href={blogPreviewCategory.link}>{blogPreviewCategory.name}</Flag>
                                    )}
                                </Fragment>
                            ))}
                            <ExtendedNextLink type="blogArticle" href={blogSideItem.link} passHref>
                                <a className="mb-2 block font-bold leading-5 text-creamWhite no-underline hover:text-creamWhite">
                                    {blogSideItem.name}
                                </a>
                            </ExtendedNextLink>
                        </div>
                    </div>
                );
            })}
        </>
    );
};
