export const PRODUCT_DETAIL_SECTIONS_IDS = {
    overview: 'overview',
    parameters: 'parameters',
    reviews: 'reviews',
    files: 'files',
    relatedProducts: 'related-products',
} as const;

// below xl the sections use the full width, so they start at the left edge with the section tabs; from xl they keep
// the 50rem reading column, where the tabs start too
export const PRODUCT_DETAIL_SECTION_WIDTH_CLASS_NAME = 'vl:max-w-none xl:max-w-210';
