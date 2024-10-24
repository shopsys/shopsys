import { BlogSignpostItem } from './BlogSignpostItem';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryRecursiveType;
    activeItem: string;
    itemLevel: number;
    activeArticleCategoryPathUuids: string[];
};

export const Children: FC<ChildrenProps> = ({
    blogCategory,
    activeItem,
    itemLevel,
    activeArticleCategoryPathUuids,
}) => (
    <>
        {blogCategory.children?.map((blogCategoryChild) => {
            const isActive = activeArticleCategoryPathUuids.includes(blogCategoryChild.uuid);

            return (
                <BlogSignpostItem
                    key={blogCategoryChild.uuid}
                    activeArticleCategoryPathUuids={activeArticleCategoryPathUuids}
                    activeItem={activeItem}
                    blogCategory={blogCategoryChild}
                    isActive={isActive}
                    itemLevel={itemLevel + 1}
                />
            );
        })}
    </>
);
