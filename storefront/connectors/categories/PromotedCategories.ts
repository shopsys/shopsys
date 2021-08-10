import { useFetchQuery } from '../../hooks/UseFetchQuery';

export const promotedCategoriesQuery = `
        query promotedCategories {
            promotedCategories {
                name
                uuid
            }
        }
    ` as const;

type CategoryType = {
    name: string;
    uuid: string;
};

export function getPromotedCategories(): CategoryType[] | undefined {
    const result = useFetchQuery({ query: promotedCategoriesQuery });
    return result?.data?.promotedCategories;
}
