import { renderHook } from '@testing-library/react';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmUserStatus } from 'gtm/enums/GtmUserStatus';
import { GtmUserType } from 'gtm/enums/GtmUserType';
import { getGtmPageReadyEvent } from 'gtm/factories/getGtmPageReadyEvent';
import { getGtmPageInfoType } from 'gtm/utils/getGtmPageInfoType';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { defaultContactInformationState } from 'store/slices/createContactInformationSlice';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

const { currentCustomerUserQueryState, gtmSafePushEventMock, setDidPageReadyRunMock } = vi.hoisted(() => ({
    currentCustomerUserQueryState: { fetching: false, stale: false },
    gtmSafePushEventMock: vi.fn(),
    setDidPageReadyRunMock: vi.fn(),
}));

vi.mock('graphql/requests/customer/queries/CurrentCustomerUserQuery.generated', () => ({
    useCurrentCustomerUserQuery: () => [currentCustomerUserQueryState],
}));

vi.mock('gtm/context/GtmProvider', () => ({
    useGtmContext: () => ({ isScriptLoaded: true, setDidPageReadyRun: setDidPageReadyRunMock }),
}));

vi.mock('gtm/utils/gtmSafePushEvent', () => ({
    gtmSafePushEvent: gtmSafePushEventMock,
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ asPath: '/search?q=a' }),
}));

const visitorPageReadyEvent = getGtmPageReadyEvent(
    getGtmPageInfoType(GtmPageType.search_results),
    null,
    true,
    undefined,
    defaultContactInformationState.contactInformation,
    defaultTestDomainConfig,
    null,
);

const companyCustomerPageReadyEvent = {
    ...visitorPageReadyEvent,
    user: { ...visitorPageReadyEvent.user, status: GtmUserStatus.customer, type: GtmUserType.b2b },
};

describe('useGtmPageReadyEvent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        currentCustomerUserQueryState.fetching = false;
        currentCustomerUserQueryState.stale = false;
    });

    test.each(['fetching', 'stale'] as const)(
        'waits while the current customer is %s and then sends the event with the loaded customer',
        (currentCustomerUserQueryFlag) => {
            currentCustomerUserQueryState[currentCustomerUserQueryFlag] = true;
            const { rerender } = renderHook(({ pageReadyEvent }) => useGtmPageReadyEvent(pageReadyEvent), {
                initialProps: { pageReadyEvent: visitorPageReadyEvent },
            });

            expect(gtmSafePushEventMock).not.toHaveBeenCalled();
            expect(setDidPageReadyRunMock).not.toHaveBeenCalled();

            currentCustomerUserQueryState[currentCustomerUserQueryFlag] = false;
            rerender({ pageReadyEvent: companyCustomerPageReadyEvent });

            expect(gtmSafePushEventMock).toHaveBeenCalledTimes(1);
            expect(gtmSafePushEventMock).toHaveBeenCalledWith(companyCustomerPageReadyEvent);
            expect(setDidPageReadyRunMock).toHaveBeenCalledWith(true);
        },
    );

    test('sends the visitor event once when no customer is loading', () => {
        const { rerender } = renderHook(({ pageReadyEvent }) => useGtmPageReadyEvent(pageReadyEvent), {
            initialProps: { pageReadyEvent: visitorPageReadyEvent },
        });

        rerender({ pageReadyEvent: { ...visitorPageReadyEvent } });

        expect(gtmSafePushEventMock).toHaveBeenCalledTimes(1);
        expect(gtmSafePushEventMock).toHaveBeenCalledWith(visitorPageReadyEvent);
    });
});
