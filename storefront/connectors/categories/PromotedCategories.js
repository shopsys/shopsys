export const promotedCategoriesQueryObject = `
        query promotedCategories {
            promotedCategories {
                name
                uuid
            }
        }
    `;

export function mapPromotedCategories(data) {
    const categories = data?.promotedCategories;
    if (!categories) {
        return [];
    }

    const mapped = [];
    categories.map((category) => {
        mapped.push({
            uuid: category.uuid,
            name: category.name,
        });
    });
    return mapped;
}
