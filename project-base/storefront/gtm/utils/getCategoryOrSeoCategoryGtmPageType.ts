import { GtmPageType } from 'gtm/enums/GtmPageType';

export const getCategoryOrSeoCategoryGtmPageType = (
    originalCategorySlug: string | null,
): GtmPageType.seo_category_detail | GtmPageType.category_detail =>
    originalCategorySlug ? GtmPageType.seo_category_detail : GtmPageType.category_detail;
