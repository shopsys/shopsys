import { TypeCompactProductFragment } from 'graphql/requests/products/fragments/CompactProductFragment.generated';
import { GtmListedProductType } from 'gtm/types/objects';
import { mapGtmProductInterface } from './mapGtmProductInterface';

export const mapGtmListedProductType = (
    product: TypeCompactProductFragment,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});
