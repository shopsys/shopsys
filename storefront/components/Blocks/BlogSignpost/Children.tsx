import { BlogSignpostIcon } from './BlogSignpostIcon';
import { BlogSignpostItemStyled } from 'components/Blocks/BlogSignpost/BlogSignpost.style';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { ListedBlogCategoryType } from 'types/blogCategory';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryType;
    activeItem: string;
    itemLevel: number;
};

const TEST_IDENTIFIER = 'blocks-blogsignpost-children-';

export const Children: FC<ChildrenProps> = ({ blogCategory, activeItem, itemLevel }) => (
    <>
        {blogCategory.children?.map((blogCategoryChild, index) => {
            const isActive = activeItem === blogCategoryChild.uuid;

            return (
                <Fragment key={blogCategoryChild.uuid}>
                    <NextLink href={blogCategoryChild.link} passHref>
                        <BlogSignpostItemStyled
                            isActive={isActive}
                            itemLevel={itemLevel}
                            data-testid={TEST_IDENTIFIER + index}
                        >
                            <BlogSignpostIcon isActive={isActive} />
                            {blogCategoryChild.name}
                        </BlogSignpostItemStyled>
                    </NextLink>
                    {blogCategoryChild.children !== undefined && blogCategoryChild.children.length > 0 && (
                        <Children blogCategory={blogCategoryChild} activeItem={activeItem} itemLevel={itemLevel + 1} />
                    )}
                </Fragment>
            );
        })}
    </>
);
