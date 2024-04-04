import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { isClient } from 'utils/isClient';
import { v4 as uuidV4 } from 'uuid';

export const useSetUserId = () => {
    const currentUserId = usePersistStore((store) => store.userId);
    const updateUserId = usePersistStore((store) => store.updateUserId);
    const isStoreHydrated = isClient && usePersistStore.persist.hasHydrated();

    useEffect(() => {
        if (isStoreHydrated && !currentUserId) {
            updateUserId(uuidV4());
        }
    }, [isStoreHydrated]);
};
