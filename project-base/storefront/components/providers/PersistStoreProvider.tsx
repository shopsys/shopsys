import { createContext, ReactNode, useMemo } from 'react';
import { broadcast } from 'store/broadcast';
import { AuthLoadingSlice, createAuthLoadingSlice, defaultAuthLoadingState } from 'store/slices/createAuthLoadingSlice';
import {
    ContactInformationSlice,
    createContactInformationSlice,
    defaultContactInformationState,
} from 'store/slices/createContactInformationSlice';
import { createPacketerySlice, defaultPacketeryState, PacketerySlice } from 'store/slices/createPacketerySlice';
import { createUserSlice, defaultUserState, UserSlice } from 'store/slices/createUserSlice';
import { logException } from 'utils/errors/logException';
import { isClient } from 'utils/isClient';
import { create, StoreApi } from 'zustand';
import { persist } from 'zustand/middleware';
import { useDomainConfig } from './DomainConfigProvider';

export type PersistStore = AuthLoadingSlice & UserSlice & ContactInformationSlice & PacketerySlice;

export const PersistStoreContext = createContext<StoreApi<PersistStore> | null>(null);

type PersistStoreProviderProps = {
    children: ReactNode;
};

const PERSIST_STORE_NAME = 'shopsys-platform-persist-store';
const PERSIST_STORE_VERSION = 3;

const createPersistStore = (domainId: number) => {
    const storeName = `${PERSIST_STORE_NAME}-${domainId}`;

    // Check if a domain-specific store already exists
    const domainStoreExists = isClient && localStorage.getItem(storeName);

    // If a domain store doesn't exist, try to migrate from the legacy store
    let initialState: Partial<PersistStore> | undefined;
    if (!domainStoreExists) {
        const legacyData = migrateFromLegacyStore();
        if (legacyData) {
            initialState = {
                ...defaultAuthLoadingState,
                ...defaultUserState,
                ...defaultContactInformationState,
                ...defaultPacketeryState,
                ...legacyData,
            };

            // Pre-populate the domain-specific store with legacy data
            try {
                localStorage.setItem(
                    storeName,
                    JSON.stringify({
                        state: initialState,
                        version: PERSIST_STORE_VERSION,
                    }),
                );
            } catch (error) {
                logException(error);
            }
        }
    }

    return create<PersistStore>()(
        persist(
            broadcast(
                (...store) => ({
                    ...createAuthLoadingSlice(...store),
                    ...createUserSlice(...store),
                    ...createContactInformationSlice(...store),
                    ...createPacketerySlice(...store),
                }),
                storeName,
                domainId,
            ),
            {
                name: storeName,
                version: PERSIST_STORE_VERSION,
                migrate: (persistedState, version) => {
                    let migratedPersistedState = { ...(persistedState as object) };

                    // Migration from version 0 (no version) to version 1
                    if (version < 1) {
                        migratedPersistedState = {
                            ...defaultAuthLoadingState,
                            ...defaultUserState,
                            ...defaultContactInformationState,
                            ...defaultPacketeryState,
                        };
                    }

                    // Migration from version 1/2 to version 3 - adds phone prefix fields
                    if (version < 3) {
                        const stored = migratedPersistedState as any;
                        migratedPersistedState = {
                            ...migratedPersistedState,
                            contactInformation: {
                                ...defaultContactInformationState.contactInformation,
                                ...stored.contactInformation,
                            },
                        };
                    }

                    return migratedPersistedState as PersistStore;
                },
            },
        ),
    );
};

export const PersistStoreProvider: FC<PersistStoreProviderProps> = ({ children }) => {
    const domainConfig = useDomainConfig();
    // Keep useMemo - createPersistStore has side effects (localStorage access, migrations)
    const store = useMemo(() => createPersistStore(domainConfig.domainId), [domainConfig.domainId]);

    return <PersistStoreContext.Provider value={store}>{children}</PersistStoreContext.Provider>;
};

const migrateFromLegacyStore = (): Partial<PersistStore> | null => {
    if (!isClient) {
        return null;
    }

    try {
        const legacyData = localStorage.getItem(PERSIST_STORE_NAME);
        if (!legacyData) {
            return null;
        }

        const parsedData = JSON.parse(legacyData);
        if (!parsedData?.state) {
            return null;
        }

        return parsedData.state;
    } catch (error) {
        logException(error);

        return null;
    }
};
