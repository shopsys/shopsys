import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { GtmSendFormEventType } from 'gtm/types/events';

export const getGtmSendFormEvent = (form: GtmFormType): GtmSendFormEventType => ({
    event: GtmEventType.send_form,
    eventParameters: {
        form,
    },
    _clear: true,
});
