import { SimpleBlogCategoryFragment } from 'graphql/requests/blogCategories/fragments/SimpleBlogCategoryFragment.generated';

export type ListedBlogCategoryRecursiveType = SimpleBlogCategoryFragment & {
    children?: ListedBlogCategoryRecursiveType[];
};
