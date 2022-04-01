import { PacketeryMakeRequestFunction, PacketeryPickFunction } from 'helpers/packetery/types';

export {};

declare global {
    interface Window {
        Packeta: {
            Viewport: {
                element: null;
                originalValue: null;
                set: () => void;
                restore: () => void;
            };
            Util: {
                makeRequest: PacketeryMakeRequestFunction;
            };
            Widget: {
                baseUrl: string;
                healthUrl: string;
                versions: {
                    v5: 'v5';
                    v6: 'v6';
                };
                close: () => void;
                pick: PacketeryPickFunction;
            };
        };
        dataLayer: any[] | undefined;
    }
}
