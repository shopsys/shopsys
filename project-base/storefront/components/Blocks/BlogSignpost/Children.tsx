import { BlogSignpostIcon } from './BlogSignpostIcon';
import { BlogSignpostItem } from './BlogSignpostItem';
import { Fragment } from 'react';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryRecursiveType;
    activeItem: string;
    itemLevel: number;
};

export const Children: FC<ChildrenProps> = ({ blogCategory, activeItem, itemLevel }) => (
    <>
        {blogCategory.children?.map((blogCategoryChild) => {
            const isActive = activeItem === blogCategoryChild.uuid;

            return (
                <Fragment key={blogCategoryChild.uuid}>
                    <BlogSignpostItem href={blogCategoryChild.link} isActive={isActive} itemLevel={itemLevel}>
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
