import { BlogSignpostItemIconStyled, BlogSignpostItemStyled } from 'components/Blocks/BlogSignpost/BlogSignpost.style';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { ListedBlogCategoryType } from 'types/blogCategory';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryType;
    activeItem: string;
    itemLevel: number;
};

const TEST_IDENTIFIER = 'blocks-blogsignpost-children-';

export const Children: FC<ChildrenProps> = ({ blogCategory, activeItem, itemLevel }) => {
    return (
        <>
            {blogCategory.children.map((blogCategoryChild, index) => (
                <Fragment key={blogCategoryChild.uuid}>
                    <NextLink href={blogCategoryChild.link} passHref>
                        <BlogSignpostItemStyled
                            isActive={activeItem === blogCategoryChild.uuid}
                            itemLevel={itemLevel}
                            data-testid={TEST_IDENTIFIER + index}
                        >
                            <BlogSignpostItemIconStyled
                                iconType="icon"
                                icon="Arrow"
                                isActive={activeItem === blogCategoryChild.uuid}
                            />
                            {blogCategoryChild.name}
                        </BlogSignpostItemStyled>
                    </NextLink>
                    {blogCategoryChild.children.length > 0 && (
                        <Children blogCategory={blogCategoryChild} activeItem={activeItem} itemLevel={itemLevel + 1} />
                    )}
                </Fragment>
            ))}
        </>
    );
};
