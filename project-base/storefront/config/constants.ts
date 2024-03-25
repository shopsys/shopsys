import { ProductOrderingModeEnum } from 'graphql/types';

export const DEFAULT_PAGE_SIZE = 9;
export const DEFAULT_SORT = ProductOrderingModeEnum.Priority as const;
/**
 * For those that are set to "true", we optimistically navigate out from a SEO category when a value of that type is changed
 * This setting needs to mirror the API functionality in the following way
 * - if a filter type "blocks" SEO category on API, it needs to be set as SEO sensitive
 * - if a filter type "allows" SEO category on API, it needs to be set as SEO insensitive
 *
 * @example
 * if the current URL is a SEO category "/my-seo-category" and sorting (which is SEO sensitive)
 * is changed, we navigate right away to "/my-normal-category?sort=NEW_SORTING"
 *
 * if the current URL is a SEO category "/my-seo-category" and availability (which is SEO insensitive)
 * is changed, we stay in the SEO category and navigate to "/my-seo-category?onlyInStock=true"
 */
export const SEO_SENSITIVE_FILTERS = {
    SORT: true,
    AVAILABILITY: false,
    PRICE: false,
    FLAGS: true,
    BRANDS: false,
    PARAMETERS: {
        CHECKBOX: true,
        SLIDER: false,
    },
};
