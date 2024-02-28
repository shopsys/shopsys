import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useRef } from 'react';
import { CookiesStoreState, useCookiesStore } from 'store/useCookiesStore';
import { usePersistStore } from 'store/usePersistStore';
import { v4 as uuidV4 } from 'uuid';

export const useSetInitialStoreValues = ({ cookiesStore }: ServerSidePropsType) => {
    const isStoreSet = useRef(false);

    const updateUserId = usePersistStore((store) => store.updateUserId);
    const setCookieStoreValue = useCookiesStore((state) => state.setCookiesStoreState);

    const setCookiesStoresValues = () => {
        const cookieStore: CookiesStoreState = cookiesStore ? JSON.parse(cookiesStore) : {};

        setCookieStoreValue(cookieStore);
    };

    const setPersistStoreValues = () => {
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        usePersistStore.persist?.onFinishHydration(({ userId }) => {
            if (!userId) {
                updateUserId(uuidV4());
            }
        });
    };

    if (!isStoreSet.current) {
        setPersistStoreValues();
        setCookiesStoresValues();

        isStoreSet.current = true;
    }
};
