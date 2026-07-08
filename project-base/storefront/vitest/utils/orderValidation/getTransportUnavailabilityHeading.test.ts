import { getTransportUnavailabilityHeading } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeTransportUnavailabilityReasonInCartEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

const mockT = vi.fn((key: string) => key) as any;

describe('getTransportUnavailabilityHeading test', () => {
    test('returns the personal pickup heading for the PersonalPickupRequired reason', () => {
        expect(
            getTransportUnavailabilityHeading(
                TypeTransportUnavailabilityReasonInCartEnum.PersonalPickupRequired,
                mockT,
            ),
        ).toBe('These products can only be picked up personally:');
    });

    test('returns the generic heading for the ExcludedForProduct reason', () => {
        expect(
            getTransportUnavailabilityHeading(TypeTransportUnavailabilityReasonInCartEnum.ExcludedForProduct, mockT),
        ).toBe('These products cannot be delivered using this transport:');
    });

    test('falls back to the generic heading for an unknown reason', () => {
        expect(
            getTransportUnavailabilityHeading('unknown_reason' as TypeTransportUnavailabilityReasonInCartEnum, mockT),
        ).toBe('These products cannot be delivered using this transport:');
    });
});
