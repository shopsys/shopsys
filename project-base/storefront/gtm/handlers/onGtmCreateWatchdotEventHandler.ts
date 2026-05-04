import { getGtmCreateWatchDogEvent } from 'gtm/factories/getGtmCreateWatchDogEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { WatchdogFormType } from 'types/form';
import { WatchDogProductType } from 'types/product';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const onGtmCreateWatchdotEventHandler = (
    watchdogFormData: WatchdogFormType,
    product: WatchDogProductType,
    domainConfig: DomainConfigType,
    arePricesHidden: boolean,
    listIndex?: number,
): void => {
    gtmSafePushEvent(getGtmCreateWatchDogEvent(watchdogFormData, product, domainConfig, arePricesHidden, listIndex));
};
