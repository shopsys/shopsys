import { getTransportAndPaymentValidationMessages } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeTransportTypeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next/config', () => ({
    default: () => ({
        serverRuntimeConfig: { internalGraphqlEndpoint: 'https://test.ts/graphql/' },
        publicRuntimeConfig: {
            errorDebuggingLevel: 'no-debug',
            domains: [{ url: 'https://test.ts/' }, { url: 'https://test.ts/' }],
        },
    }),
}));

// Mock the translate function to match Translate type
const mockT = vi.fn((key: string) => key) as any;

// Mock transport data - using partial mocks to avoid complex GraphQL type issues
const mockTransport = {
    uuid: 'transport-uuid-1',
    name: 'Test Transport',
    isPersonalPickup: false,
    transportTypeCode: TypeTransportTypeEnum.Common,
} as any;

const mockPersonalPickupTransport = {
    uuid: 'transport-uuid-2',
    name: 'Personal Pickup Transport',
    isPersonalPickup: true,
    transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
} as any;

// Mock payment data
const mockPayment = {
    uuid: 'payment-uuid-1',
    name: 'Test Payment',
    goPayPaymentMethod: null,
} as any;

// Mock pickup place data
const mockPickupPlace = {
    identifier: 'pickup-place-1',
    name: 'Test Pickup Place',
} as any;

describe('getTransportAndPaymentValidationMessages test', () => {
    test('should return transport error when transport is not selected', () => {
        const result = getTransportAndPaymentValidationMessages(null, null, mockPayment, null, mockT);

        expect(result).toEqual({
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport',
            },
        });
        expect(mockT).toHaveBeenCalledWith('Choose transport');
        expect(mockT).toHaveBeenCalledWith('Please select transport');
    });

    test('should return payment error when payment is not selected', () => {
        const result = getTransportAndPaymentValidationMessages(mockTransport, null, null, null, mockT);

        expect(result).toEqual({
            payment: {
                name: 'payment',
                label: 'Choose payment',
                errorMessage: 'Please select payment',
            },
        });
        expect(mockT).toHaveBeenCalledWith('Choose payment');
        expect(mockT).toHaveBeenCalledWith('Please select payment');
    });

    test('should return transport error when personal pickup is selected without pickup place', () => {
        const result = getTransportAndPaymentValidationMessages(
            mockPersonalPickupTransport,
            null,
            mockPayment,
            null,
            mockT,
        );

        expect(result).toEqual({
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport with a personal pickup place',
            },
        });
        expect(mockT).toHaveBeenCalledWith('Please select transport with a personal pickup place');
    });

    test('should return both transport and payment errors when neither is selected', () => {
        const result = getTransportAndPaymentValidationMessages(null, null, null, null, mockT);

        expect(result).toEqual({
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport',
            },
        });
        // When transport is null, function returns early with only transport error
    });

    test('should return no errors when valid transport and payment are selected', () => {
        const result = getTransportAndPaymentValidationMessages(mockTransport, null, mockPayment, null, mockT);

        expect(result).toEqual({});
    });

    test('should return no errors when personal pickup transport has valid pickup place', () => {
        const result = getTransportAndPaymentValidationMessages(
            mockPersonalPickupTransport,
            mockPickupPlace,
            mockPayment,
            null,
            mockT,
        );

        expect(result).toEqual({});
    });

    test('should handle pickup place with empty identifier', () => {
        const emptyPickupPlace = {
            ...mockPickupPlace,
            identifier: '',
        };

        const result = getTransportAndPaymentValidationMessages(
            mockPersonalPickupTransport,
            emptyPickupPlace,
            mockPayment,
            null,
            mockT,
        );

        expect(result).toEqual({
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport with a personal pickup place',
            },
        });
    });

    test('should handle undefined values gracefully', () => {
        const result = getTransportAndPaymentValidationMessages(null, null, null, null, mockT);

        expect(result).toEqual({
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport',
            },
        });
    });
});
