import { useSyncExternalStore } from 'react';

const subscribe = (onChange: () => void) => {
    document.addEventListener('visibilitychange', onChange);
    return () => document.removeEventListener('visibilitychange', onChange);
};
const getSnapshot = () => !document.hidden;
const getServerSnapshot = () => false;

export const usePageVisibility = (): boolean => useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);
