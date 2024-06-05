import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { StateCreator } from 'zustand';

type PacketeryState = {
    packeteryPickupPoint: TypeListedStoreFragment | null;
};

export type PacketerySlice = PacketeryState & {
    setPacketeryPickupPoint: (mappedPacketeryPoint: TypeListedStoreFragment) => void;
    clearPacketeryPickupPoint: () => void;
};

export const defaultPacketeryState = {
    packeteryPickupPoint: null,
};

export const createPacketerySlice: StateCreator<PacketerySlice> = (set) => ({
    ...defaultPacketeryState,

    setPacketeryPickupPoint: (packeteryPickupPoint) => {
        set({ packeteryPickupPoint });
    },
    clearPacketeryPickupPoint: () => {
        set({ packeteryPickupPoint: null });
    },
});
