import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { StateCreator } from 'zustand';

type PacketeryState = {
    packeteryPickupPoint: StoreOrPacketeryPoint | null;
};

export type PacketerySlice = PacketeryState & {
    setPacketeryPickupPoint: (mappedPacketeryPoint: StoreOrPacketeryPoint) => void;
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
