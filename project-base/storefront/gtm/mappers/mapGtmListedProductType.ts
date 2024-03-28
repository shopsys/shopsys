import { mapGtmProductInterface } from './mapGtmProductInterface';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeSimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { GtmListedProductType } from 'gtm/types/objects';

export const mapGtmListedProductType = (
    product: TypeListedProductFragment | TypeSimpleProductFragment,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});
