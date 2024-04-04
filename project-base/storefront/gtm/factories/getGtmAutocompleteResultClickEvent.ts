import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { GtmAutocompleteResultClickEventType } from 'gtm/types/events';

export const getGtmAutocompleteResultClickEvent = (
    keyword: string,
    section: GtmSectionType,
    itemName: string,
): GtmAutocompleteResultClickEventType => ({
    event: GtmEventType.autocomplete_result_click,
    autocompleteResultClick: {
        keyword,
        itemName,
        section,
    },
    _clear: true,
});
