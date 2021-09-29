import { CategoryItemApiType, CategoryItemType } from 'components/Blocks/Categories/CategoryItem/types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';

export const promotedCategoriesQuery = `
        query promotedCategories {
            promotedCategories {
                uuid
                name
                slug
                images(sizes: "default") {
                    sizes {
                        url
                        width
                        height
                    }
                }
            }
        }
    ` as const;

const mapCategoryApiData = (apiData: CategoryItemApiType[]) => {
    return apiData.map((apiCategory) => {
        return {
            ...apiCategory,
            image: apiCategory.images.length > 0 ? apiCategory.images[0].sizes[0] : null,
        };
    });
};

export function getPromotedCategories(): CategoryItemType[] | undefined {
    const result = useFetchQuery({ query: promotedCategoriesQuery });
    const apiData = result?.data?.promotedCategories;
    if (apiData === undefined) {
        return undefined;
    }

    return mapCategoryApiData(apiData);
}
