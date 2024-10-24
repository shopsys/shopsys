import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

export const findActiveBlogCategoryPath = (
    blogCategory: ListedBlogCategoryRecursiveType[] | undefined,
    activeItem: string,
): string[] => {
    for (const category of blogCategory || []) {
        if (category.uuid === activeItem) {
            return [category.uuid];
        }

        if (category.children?.length) {
            const result = findActiveBlogCategoryPath(category.children, activeItem);
            if (result.length > 0) {
                return [category.uuid, ...result];
            }
        }
    }

    return [];
};
