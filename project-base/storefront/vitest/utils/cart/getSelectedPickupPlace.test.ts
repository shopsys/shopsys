import { TypeTransportTypeEnum } from 'graphql/types';
import { getSelectedPickupPlace } from 'utils/cart/pickupPlaceCalculations';
import { describe, expect, it } from 'vitest';

describe('getSelectedPickupPlace', () => {
    const mockPacketeryPoint = {
        identifier: 'packetery-123',
        name: 'Packetery Point',
        city: 'Prague',
        street: 'Main St 123',
    } as any;

    const mockStorePoint = {
        identifier: 'store-456',
        name: 'Store Point',
        city: 'Brno',
        street: 'Store St 456',
    };

    const mockTransportWithStores = {
        transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
        stores: {
            edges: [{ node: mockStorePoint }, { node: { identifier: 'store-789', name: 'Another Store' } }],
        },
    } as any;

    const mockPacketeryTransport = {
        transportTypeCode: TypeTransportTypeEnum.Packetery,
        stores: null,
    } as any;

    describe('when transport or pickupPlaceIdentifier is missing', () => {
        it('should return null when transport is null', () => {
            const result = getSelectedPickupPlace(null, 'some-id', mockPacketeryPoint);

            expect(result).toBeNull();
        });

        it('should return null when transport is undefined', () => {
            const result = getSelectedPickupPlace(undefined, 'some-id', mockPacketeryPoint);

            expect(result).toBeNull();
        });

        it('should return null when pickupPlaceIdentifier is null', () => {
            const result = getSelectedPickupPlace(mockTransportWithStores, null, mockPacketeryPoint);

            expect(result).toBeNull();
        });

        it('should return null when pickupPlaceIdentifier is undefined', () => {
            const result = getSelectedPickupPlace(mockTransportWithStores, undefined, mockPacketeryPoint);

            expect(result).toBeNull();
        });

        it('should return null when pickupPlaceIdentifier is empty string', () => {
            const result = getSelectedPickupPlace(mockTransportWithStores, '', mockPacketeryPoint);

            expect(result).toBeNull();
        });
    });

    describe('when transport is Packetery type', () => {
        it('should return packeteryPickupPoint for Packetery transport', () => {
            const result = getSelectedPickupPlace(mockPacketeryTransport, 'packetery-123', mockPacketeryPoint);

            expect(result).toBe(mockPacketeryPoint);
        });

        it('should return packeteryPickupPoint even if identifier does not match', () => {
            const result = getSelectedPickupPlace(mockPacketeryTransport, 'different-id', mockPacketeryPoint);

            expect(result).toBe(mockPacketeryPoint);
        });

        it('should return null packeteryPickupPoint if it is null', () => {
            const result = getSelectedPickupPlace(mockPacketeryTransport, 'packetery-123', null);

            expect(result).toBeNull();
        });
    });

    describe('when transport has stores', () => {
        it('should return matching store from transport.stores', () => {
            const result = getSelectedPickupPlace(mockTransportWithStores, 'store-456', mockPacketeryPoint);

            expect(result).toBe(mockStorePoint);
        });

        it('should return matching store even when packeteryPickupPoint is provided', () => {
            const result = getSelectedPickupPlace(mockTransportWithStores, 'store-456', mockPacketeryPoint);

            expect(result).toBe(mockStorePoint);
            expect(result).not.toBe(mockPacketeryPoint);
        });

        it('should return null when store identifier is not found', () => {
            const result = getSelectedPickupPlace(mockTransportWithStores, 'non-existent-store', mockPacketeryPoint);

            expect(result).toBeNull();
        });

        it('should return null when stores edges is empty', () => {
            const transportWithEmptyStores = {
                transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
                stores: { edges: [] },
            } as any;

            const result = getSelectedPickupPlace(transportWithEmptyStores, 'store-456', mockPacketeryPoint);

            expect(result).toBeNull();
        });

        it('should return null when stores is null', () => {
            const transportWithNullStores = {
                transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
                stores: null,
            } as any;

            const result = getSelectedPickupPlace(transportWithNullStores, 'store-456', mockPacketeryPoint);

            expect(result).toBeNull();
        });
    });

    describe('edge cases', () => {
        it('should handle store edge with null node', () => {
            const transportWithNullNode = {
                transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
                stores: {
                    edges: [{ node: null }, { node: mockStorePoint }],
                },
            } as any;

            const result = getSelectedPickupPlace(transportWithNullNode, 'store-456', mockPacketeryPoint);

            expect(result).toBe(mockStorePoint);
        });

        it('should handle store edge with undefined node', () => {
            const transportWithUndefinedNode = {
                transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
                stores: {
                    edges: [{ node: undefined }, { node: mockStorePoint }],
                },
            } as any;

            const result = getSelectedPickupPlace(transportWithUndefinedNode, 'store-456', mockPacketeryPoint);

            expect(result).toBe(mockStorePoint);
        });

        it('should return matching store node with partial properties', () => {
            const transportWithMatchingNode = {
                transportTypeCode: TypeTransportTypeEnum.PersonalPickup,
                stores: {
                    edges: [{ node: { identifier: 'store-456' } }],
                },
            } as any;

            const result = getSelectedPickupPlace(transportWithMatchingNode, 'store-456', mockPacketeryPoint);

            expect(result).toEqual({ identifier: 'store-456' });
        });
    });
});
