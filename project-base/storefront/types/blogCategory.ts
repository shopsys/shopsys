import { SimpleBlogCategoryFragmentApi } from 'graphql/requests/blogCategories/fragments/SimpleBlogCategoryFragment.generated';

export type ListedBlogCategoryRecursiveType = SimpleBlogCategoryFragmentApi & {
    children?: ListedBlogCategoryRecursiveType[];
};
