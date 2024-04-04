import { mapGtmProductInterface } from './mapGtmProductInterface';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { SimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { GtmListedProductType } from 'gtm/types/objects';

export const mapGtmListedProductType = (
    product: ListedProductFragment | SimpleProductFragment,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});
