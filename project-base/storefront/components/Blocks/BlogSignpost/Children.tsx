import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { BlogSignpostItem } from './BlogSignpostItem';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryRecursiveType;
    activeItem: string;
    itemLevel: number;
    activeArticleCategoryPathUuids: string[];
    handleToggle: (uuids: string[]) => void;
    openUuids: string[];
};

export const Children: FC<ChildrenProps> = ({
    blogCategory,
    activeItem,
    itemLevel,
    activeArticleCategoryPathUuids,
    handleToggle,
    openUuids,
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
                    handleToggle={handleToggle}
                    isActive={isActive}
                    itemLevel={itemLevel + 1}
                    openUuids={openUuids}
                />
            );
        })}
    </>
);
