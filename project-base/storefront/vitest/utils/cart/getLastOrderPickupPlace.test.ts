import { getGtmPickupPlaceFromLastOrder } from 'gtm/mappers/getGtmPickupPlaceFromLastOrder';
import { getGtmPickupPlaceFromStore } from 'gtm/mappers/getGtmPickupPlaceFromStore';
// Import after mocking
import { getLastOrderPickupPlace, PICKUP_POINT_NOT_SET_ERROR_MESSAGE } from 'utils/cart/pickupPlaceCalculations';
import { beforeEach, describe, expect, it, vi } from 'vitest';

// Mock the external dependencies BEFORE importing the function
vi.mock('gtm/mappers/getGtmPickupPlaceFromStore', () => ({
    getGtmPickupPlaceFromStore: vi.fn(),
}));

vi.mock('gtm/mappers/getGtmPickupPlaceFromLastOrder', () => ({
    getGtmPickupPlaceFromLastOrder: vi.fn(),
}));

vi.mock('next/config', () => ({
    default: () => ({
        serverRuntimeConfig: { internalGraphqlEndpoint: 'https://test.ts/graphql/' },
        publicRuntimeConfig: {
            errorDebuggingLevel: 'no-debug',
            domains: [{ url: 'https://test.ts/' }, { url: 'https://test.ts/' }],
        },
    }),
}));

// Type the mocked functions
const mockGetGtmPickupPlaceFromStore = vi.mocked(getGtmPickupPlaceFromStore);
const mockGetGtmPickupPlaceFromLastOrder = vi.mocked(getGtmPickupPlaceFromLastOrder);

describe('getLastOrderPickupPlace', () => {
    const mockLastOrder = {
        uuid: 'order-123',
        pickupPlaceIdentifier: 'pickup-456',
        deliveryCity: 'Prague',
        deliveryStreet: 'Test Street 123',
        deliveryPostcode: '12345',
        deliveryCountry: {
            name: 'Czech Republic',
            code: 'CZ',
        },
    } as any;

    const mockPacketeryPoint = {
        identifier: 'pickup-456',
        name: 'Packetery Point',
        city: 'Prague',
        street: 'Main St 123',
    } as any;

    const mockApiPickupPlace = {
        identifier: 'api-pickup-789',
        name: 'API Store',
        city: 'Brno',
        street: 'API St 789',
    } as any;

    const mockGtmStoreResult = {
        identifier: 'gtm-store-123',
        name: 'GTM Store',
    } as any;

    const mockGtmLastOrderResult = {
        identifier: 'gtm-last-order-456',
        name: 'GTM Last Order',
    } as any;

    beforeEach(() => {
        vi.clearAllMocks();
        mockGetGtmPickupPlaceFromStore.mockReturnValue(mockGtmStoreResult);
        mockGetGtmPickupPlaceFromLastOrder.mockReturnValue(mockGtmLastOrderResult);
    });

    describe('when packeteryPickupPoint matches identifier', () => {
        it('should return packeteryPickupPoint when identifiers match exactly', () => {
            const result = getLastOrderPickupPlace(mockLastOrder, 'pickup-456', mockApiPickupPlace, mockPacketeryPoint);

            expect(result).toBe(mockPacketeryPoint);
            expect(mockGetGtmPickupPlaceFromStore).not.toHaveBeenCalled();
            expect(mockGetGtmPickupPlaceFromLastOrder).not.toHaveBeenCalled();
        });

        it('should prioritize packeteryPickupPoint over API data when identifiers match', () => {
            const result = getLastOrderPickupPlace(
                mockLastOrder,
                'pickup-456',
                { ...mockApiPickupPlace, identifier: 'different-id' } as any,
                mockPacketeryPoint,
            );

            expect(result).toBe(mockPacketeryPoint);
            expect(mockGetGtmPickupPlaceFromStore).not.toHaveBeenCalled();
        });
    });

    describe('when packeteryPickupPoint does not match identifier', () => {
        const nonMatchingPacketeryPoint = {
            ...mockPacketeryPoint,
            identifier: 'different-identifier',
        } as any;

        it('should use API pickup place when available', () => {
            const result = getLastOrderPickupPlace(
                mockLastOrder,
                'pickup-456',
                mockApiPickupPlace,
                nonMatchingPacketeryPoint,
            );

            expect(result).toBe(mockGtmStoreResult);
            expect(mockGetGtmPickupPlaceFromStore).toHaveBeenCalledWith(mockApiPickupPlace);
            expect(mockGetGtmPickupPlaceFromLastOrder).not.toHaveBeenCalled();
        });

        it('should call getGtmPickupPlaceFromStore with correct parameters', () => {
            getLastOrderPickupPlace(mockLastOrder, 'pickup-456', mockApiPickupPlace, nonMatchingPacketeryPoint);

            expect(mockGetGtmPickupPlaceFromStore).toHaveBeenCalledTimes(1);
            expect(mockGetGtmPickupPlaceFromStore).toHaveBeenCalledWith(mockApiPickupPlace);
        });

        it('should not use API pickup place when identifier is missing', () => {
            const apiPlaceWithoutIdentifier = {
                ...mockApiPickupPlace,
                identifier: null,
            } as any;

            expect(() => {
                getLastOrderPickupPlace(mockLastOrder, 'pickup-456', apiPlaceWithoutIdentifier, null);
            }).toThrow(PICKUP_POINT_NOT_SET_ERROR_MESSAGE);

            expect(mockGetGtmPickupPlaceFromStore).not.toHaveBeenCalled();
        });

        it('should not use API pickup place when identifier is undefined', () => {
            const apiPlaceWithUndefinedIdentifier = {
                ...mockApiPickupPlace,
                identifier: undefined,
            } as any;

            expect(() => {
                getLastOrderPickupPlace(mockLastOrder, 'pickup-456', apiPlaceWithUndefinedIdentifier, null);
            }).toThrow(PICKUP_POINT_NOT_SET_ERROR_MESSAGE);

            expect(mockGetGtmPickupPlaceFromStore).not.toHaveBeenCalled();
        });
    });

    describe('when API pickup place is not available', () => {
        it('should use GTM last order mapping when packeteryPickupPoint exists', () => {
            const result = getLastOrderPickupPlace(mockLastOrder, 'pickup-456', null, {
                identifier: 'different-id',
                name: 'Different Point',
            } as any);

            expect(result).toBe(mockGtmLastOrderResult);
            expect(mockGetGtmPickupPlaceFromLastOrder).toHaveBeenCalledWith('pickup-456', mockLastOrder);
            expect(mockGetGtmPickupPlaceFromStore).not.toHaveBeenCalled();
        });

        it('should throw error when packeteryPickupPoint is null', () => {
            expect(() => {
                getLastOrderPickupPlace(mockLastOrder, 'pickup-456', null, null);
            }).toThrow(PICKUP_POINT_NOT_SET_ERROR_MESSAGE);

            expect(mockGetGtmPickupPlaceFromStore).not.toHaveBeenCalled();
            expect(mockGetGtmPickupPlaceFromLastOrder).not.toHaveBeenCalled();
        });

        it('should throw error when packeteryPickupPoint is undefined', () => {
            expect(() => {
                getLastOrderPickupPlace(mockLastOrder, 'pickup-456', undefined as any, undefined as any);
            }).toThrow(PICKUP_POINT_NOT_SET_ERROR_MESSAGE);
        });

        it('should call getGtmPickupPlaceFromLastOrder with correct parameters', () => {
            getLastOrderPickupPlace(mockLastOrder, 'pickup-456', null, {
                identifier: 'different-id',
                name: 'Different Point',
            } as any);

            expect(mockGetGtmPickupPlaceFromLastOrder).toHaveBeenCalledTimes(1);
            expect(mockGetGtmPickupPlaceFromLastOrder).toHaveBeenCalledWith('pickup-456', mockLastOrder);
        });
    });

    describe('edge cases', () => {
        it('should handle empty string identifiers', () => {
            const emptyIdentifierPacketery = {
                ...mockPacketeryPoint,
                identifier: '',
            } as any;

            // Empty string identifier doesn't match, so it should fall back to GTM last order
            const result = getLastOrderPickupPlace(mockLastOrder, 'pickup-456', null, emptyIdentifierPacketery);

            expect(result).toBe(mockGtmLastOrderResult);
            expect(mockGetGtmPickupPlaceFromLastOrder).toHaveBeenCalledWith('pickup-456', mockLastOrder);
        });

        it('should handle complex last order object', () => {
            const complexLastOrder = {
                uuid: 'complex-order',
                pickupPlaceIdentifier: 'complex-pickup',
                transport: { name: 'Express Transport' },
                customer: { email: 'test@example.com' },
                deliveryCity: 'Complex City',
                deliveryStreet: 'Complex Street',
                deliveryPostcode: '54321',
                deliveryCountry: {
                    name: 'Slovakia',
                    code: 'SK',
                },
            } as any;

            getLastOrderPickupPlace(complexLastOrder, 'pickup-456', null, {
                identifier: 'different-id',
                name: 'Different Point',
            } as any);

            expect(mockGetGtmPickupPlaceFromLastOrder).toHaveBeenCalledWith('pickup-456', complexLastOrder);
        });

        it('should handle API place with additional properties', () => {
            const complexApiPlace = {
                identifier: 'complex-api-123',
                name: 'Complex API Store',
                address: { street: 'Complex St', city: 'Complex City' },
                openingHours: { monday: '9-17' },
            } as any;

            const result = getLastOrderPickupPlace(mockLastOrder, 'pickup-456', complexApiPlace, {
                identifier: 'different-id',
                name: 'Different Point',
            } as any);

            expect(result).toBe(mockGtmStoreResult);
            expect(mockGetGtmPickupPlaceFromStore).toHaveBeenCalledWith(complexApiPlace);
        });
    });
});
