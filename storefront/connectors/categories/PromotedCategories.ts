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

type ResponseCategory = {
    name: string;
    uuid: string;
};

export function mapPromotedCategories(data: { promotedCategories: ResponseCategory[] }): Category[] {
    const categories = data?.promotedCategories;

    if (!categories) {
        return [];
    }

    const mapped: Category[] = [];

    categories.map((category) => {
        mapped.push({
            uuid: category.uuid,
            name: category.name,
        });
    });

    return mapped;
}
