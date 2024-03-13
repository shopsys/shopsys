import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const usePersistStoreHydration = () => {
    useEffect(() => {
        usePersistStore.persist.rehydrate();
    }, []);
};
