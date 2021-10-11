import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';

export const blogCategoriesQuery = `
    query blogCategories {    
        blogCategories {
            uuid
            name
            link
            children {
                uuid
                name
                link
            }
        }
    }
`;

type BlogCategoriesChildrenType = {
    uuid: string;
    name: string;
    link: string;
};

export type BlogCategoriesType = {
    uuid: string;
    name: string;
    link: string;
    children: BlogCategoriesChildrenType[];
};

export function getBlogCategoriesItems(): BlogCategoriesType[] {
    const result = useFetchQuery({ query: blogCategoriesQuery });
    return result?.data?.blogCategories;
}
