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
                        isActive={isActive}
                        href={blogCategoryChild.link}
                        itemLevel={itemLevel}
                        dataTestId={TEST_IDENTIFIER + index}
                    >
                        <BlogSignpostIcon isActive={isActive} />
                        {blogCategoryChild.name}
                    </BlogSignpostItem>
                    {blogCategoryChild.children !== undefined && blogCategoryChild.children.length > 0 && (
                        <Children blogCategory={blogCategoryChild} activeItem={activeItem} itemLevel={itemLevel + 1} />
                    )}
                </Fragment>
            );
        })}
    </>
);
