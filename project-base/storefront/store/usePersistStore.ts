import { PersistStoreContext, type PersistStore } from 'components/providers/PersistStoreProvider';
import { useContext } from 'react';
import { useStore } from 'zustand';

export const usePersistStore = <T>(selector: (store: PersistStore) => T): T => {
    const persistStoreContext = useContext(PersistStoreContext);

    if (!persistStoreContext) {
        throw new Error('usePersistStore must be used within PersistStoreProvider');
    }

    return useStore(persistStoreContext, selector);
};
