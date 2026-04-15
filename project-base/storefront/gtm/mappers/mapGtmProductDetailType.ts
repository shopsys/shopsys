import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmProductInterface } from 'gtm/types/objects';
import { mapGtmProductInterface } from './mapGtmProductInterface';

export const mapGtmProductDetailType = (
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment,
    domainUrl: string,
): GtmProductInterface => mapGtmProductInterface(product, domainUrl);
