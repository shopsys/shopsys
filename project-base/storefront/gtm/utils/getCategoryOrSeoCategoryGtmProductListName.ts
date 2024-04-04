import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export const getCategoryOrSeoCategoryGtmProductListName = (
    originalCategorySlug: string | null,
): GtmProductListNameType.seo_category_detail | GtmProductListNameType.category_detail =>
    originalCategorySlug ? GtmProductListNameType.seo_category_detail : GtmProductListNameType.category_detail;
