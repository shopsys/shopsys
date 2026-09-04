import { type PersistStore, PersistStoreContext } from 'components/providers/PersistStoreProvider';
import { useContext } from 'react';
import { type StoreApi, useStore } from 'zustand';

export const usePersistStore = <T>(selector: (store: PersistStore) => T): T => {
    const persistStoreContext = useContext(PersistStoreContext);

    if (!persistStoreContext) {
        throw new Error('usePersistStore must be used within PersistStoreProvider');
    }

    return useStore(persistStoreContext, selector);
};

export const usePersistStoreApi = (): StoreApi<PersistStore> => {
    const persistStoreContext = useContext(PersistStoreContext);

    if (!persistStoreContext) {
        throw new Error('usePersistStoreApi must be used within PersistStoreProvider');
    }

    return persistStoreContext;
};
