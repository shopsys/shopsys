import { ListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { StateCreator } from 'zustand';

export type PacketerySlice = {
    packeteryPickupPoint: ListedStoreFragment | null;

    setPacketeryPickupPoint: (mappedPacketeryPoint: ListedStoreFragment) => void;
    clearPacketeryPickupPoint: () => void;
};

export const createPacketerySlice: StateCreator<PacketerySlice> = (set) => ({
    packeteryPickupPoint: null,

    setPacketeryPickupPoint: (packeteryPickupPoint) => {
        set({ packeteryPickupPoint });
    },
    clearPacketeryPickupPoint: () => {
        set({ packeteryPickupPoint: null });
    },
});
