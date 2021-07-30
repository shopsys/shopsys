import { useFetchQuery } from '../../hooks/UseFetchQuery';

export const promotedCategoriesQuery = `
        query promotedCategories {
            promotedCategories {
                name
                uuid
            }
        }
    ` as const;

type Category = {
    name: string;
    uuid: string;
};

export function getPromotedCategories(): Category[] | undefined {
    const result = useFetchQuery({ query: promotedCategoriesQuery });
    return result?.data?.promotedCategories;
}
