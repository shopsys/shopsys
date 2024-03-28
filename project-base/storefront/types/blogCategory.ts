import { TypeSimpleBlogCategoryFragment } from 'graphql/requests/blogCategories/fragments/SimpleBlogCategoryFragment.generated';

export type ListedBlogCategoryRecursiveType = TypeSimpleBlogCategoryFragment & {
    children?: ListedBlogCategoryRecursiveType[];
};
