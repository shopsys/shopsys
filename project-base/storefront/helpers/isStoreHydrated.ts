import { isClient } from './isClient';
import { usePersistStore } from 'store/usePersistStore';

export const isStoreHydrated = () => isClient && usePersistStore.persist.hasHydrated();
