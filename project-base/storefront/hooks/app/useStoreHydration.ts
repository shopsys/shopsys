import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useStoreHydration = () => {
    useEffect(() => {
        usePersistStore.persist.rehydrate();
    }, []);
};
