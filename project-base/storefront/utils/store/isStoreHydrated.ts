import { usePersistStore } from 'store/usePersistStore';
import { isClient } from 'utils/isClient';

export const isStoreHydrated = () => isClient && usePersistStore.persist.hasHydrated();
