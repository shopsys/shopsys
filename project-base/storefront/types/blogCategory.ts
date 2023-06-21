import { SimpleBlogCategoryFragmentApi } from 'graphql/generated';

export type ListedBlogCategoryRecursiveType = SimpleBlogCategoryFragmentApi & {
    children?: ListedBlogCategoryRecursiveType[];
};
