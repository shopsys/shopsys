import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { getGtmAutocompleteResultClickEvent } from 'gtm/factories/getGtmAutocompleteResultClickEvent';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';

export const onGtmAutocompleteResultClickEventHandler = (
    keyword: string,
    section: GtmSectionType,
    itemName: string,
): void => {
    gtmSafePushEvent(getGtmAutocompleteResultClickEvent(keyword, section, itemName));
};
