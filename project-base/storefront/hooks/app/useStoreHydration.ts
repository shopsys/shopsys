import { isClient } from 'helpers/isClient';
import { useRef } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const usePersistStoreHydration = () => {
    const isPersistStoreHydrated = useRef(false);

    if (!isPersistStoreHydrated.current && isClient) {
        usePersistStore.persist.rehydrate();
        isPersistStoreHydrated.current = true;
    }
};
