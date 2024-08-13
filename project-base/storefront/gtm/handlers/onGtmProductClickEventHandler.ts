import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeSimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductClickEvent } from 'gtm/factories/getGtmProductClickEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmProductClickEventHandler = (
    product: TypeListedProductFragment | TypeSimpleProductFragment,
    gtmProductListName: GtmProductListNameType,
    index: number,
    domainUrl: string,
    arePricesHidden: boolean,
): void => {
    gtmSafePushEvent(getGtmProductClickEvent(product, gtmProductListName, index, domainUrl, arePricesHidden));
};
