import { ListedStoreFragmentApi } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { StateCreator } from 'zustand';

export type PacketerySlice = {
    packeteryPickupPoint: ListedStoreFragmentApi | null;

    setPacketeryPickupPoint: (mappedPacketeryPoint: ListedStoreFragmentApi) => void;
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
