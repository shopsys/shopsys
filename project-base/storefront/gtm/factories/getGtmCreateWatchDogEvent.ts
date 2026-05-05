import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmProductInterface } from 'gtm/mappers/mapGtmProductInterface';
import { GtmCreateWatchdogEventType } from 'gtm/types/events';
import { GtmCartItemType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { WatchdogFormType } from 'types/form';
import { WatchDogProductType } from 'types/product';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const getGtmCreateWatchDogEvent = (
    watchdogFormData: WatchdogFormType,
    product: WatchDogProductType,
    domainConfig: DomainConfigType,
    arePricesHidden: boolean,
    listIndex?: number,
): GtmCreateWatchdogEventType => ({
    event: GtmEventType.create_watchdog,
    eventParameters: {
        email: watchdogFormData.email,
        productUuid: watchdogFormData.productUuid,
    },
    ecommerce: {
        currencyCode: domainConfig.currencyCode,
        valueWithoutVat: getGtmPriceBasedOnVisibility(product.price.priceWithoutVat),
        valueWithVat: getGtmPriceBasedOnVisibility(product.price.priceWithVat),
        products: [mapGtmWatchdogProduct(product, domainConfig.url, listIndex)],
        arePricesHidden,
    },
    _clear: true,
});

const mapGtmWatchdogProduct = (
    product: WatchDogProductType,
    domainUrl: string,
    listIndex?: number,
): GtmCartItemType => {
    const mappedProduct: GtmCartItemType = {
        ...mapGtmProductInterface(product, domainUrl),
        quantity: 1,
    };

    if (listIndex !== undefined) {
        mappedProduct.listIndex = listIndex + 1;
    }

    return mappedProduct;
};
