// not next/router here!
import { useRouter } from 'next/compat/router';

export function useIsAppRouter() {
    // it returns the router instance if it is rendered in the pages router
    // returns null if it is in the app router
    const router = useRouter();
    return !router;
}
