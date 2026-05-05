import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { getGtmPageReadyEvent } from 'gtm/factories/getGtmPageReadyEvent';
import { getGtmPageInfoType } from 'gtm/utils/getGtmPageInfoType';
import { defaultContactInformationState } from 'store/slices/createContactInformationSlice';
import { describe, expect, test } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

describe('getGtmPageReadyEvent', () => {
    test('should create page_ready event', () => {
        const result = getGtmPageReadyEvent(
            getGtmPageInfoType(GtmPageType.homepage),
            null,
            true,
            null,
            defaultContactInformationState.contactInformation,
            defaultTestDomainConfig,
            null,
        );

        expect(result.event).toBe(GtmEventType.page_ready);
    });
});
