import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmListedProductType } from 'gtm/types/objects';
import { mapGtmProductInterface } from './mapGtmProductInterface';

export const mapGtmListedProductType = (
    product: TypeListedProductFragment,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});
