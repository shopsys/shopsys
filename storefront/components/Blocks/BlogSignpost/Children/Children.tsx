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

const Children: FC<ChildrenProps> = (props) => {
    return (
        <>
            {props.blogCategory.children.map((blogCategoryChild, index) => (
                <Fragment key={blogCategoryChild.uuid}>
                    <NextLink href={blogCategoryChild.link} passHref>
                        <BlogSignpostItemStyled
                            isActive={props.activeItem === blogCategoryChild.uuid}
                            itemLevel={props.itemLevel}
                            data-testid={TEST_IDENTIFIER + index}
                        >
                            <BlogSignpostItemIconStyled
                                iconType="icon"
                                icon="Arrow"
                                isActive={props.activeItem === blogCategoryChild.uuid}
                            />
                            {blogCategoryChild.name}
                        </BlogSignpostItemStyled>
                    </NextLink>
                    {blogCategoryChild.children.length > 0 && (
                        <Children
                            blogCategory={blogCategoryChild}
                            activeItem={props.activeItem}
                            itemLevel={props.itemLevel + 1}
                        />
                    )}
                </Fragment>
            ))}
        </>
    );
};

export default Children;
