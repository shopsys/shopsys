import { BlogSignpostIcon } from './BlogSignpostIcon';
import { BlogSignpostItem } from './BlogSignpostItem';
import { Fragment } from 'react';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryRecursiveType;
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
                    <BlogSignpostItem
                        dataTestId={TEST_IDENTIFIER + index}
                        href={blogCategoryChild.link}
                        isActive={isActive}
                        itemLevel={itemLevel}
                    >
                        <BlogSignpostIcon isActive={isActive} />
                        {blogCategoryChild.name}
                    </BlogSignpostItem>
                    {blogCategoryChild.children !== undefined && blogCategoryChild.children.length > 0 && (
                        <Children activeItem={activeItem} blogCategory={blogCategoryChild} itemLevel={itemLevel + 1} />
                    )}
                </Fragment>
            );
        })}
    </>
);
