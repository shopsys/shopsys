import { isClient } from 'helpers/isClient';
import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useRef } from 'react';
import { CookiesStoreState, useCookiesStore } from 'store/useCookiesStore';
import { usePersistStore } from 'store/usePersistStore';
import { v4 as uuidV4 } from 'uuid';

export const useSetInitialStoreValues = ({ cookiesStore }: ServerSidePropsType) => {
    const isStoreSet = useRef(false);

    const userId = usePersistStore((store) => store.userId);
    const updateUserId = usePersistStore((store) => store.updateUserId);
    const setCookieStoreValue = useCookiesStore((state) => state.setCookiesStoreState);

    const setCookiesStoresValues = () => {
        const cookieStore: CookiesStoreState = cookiesStore ? JSON.parse(cookiesStore) : {};

        setCookieStoreValue(cookieStore);
    };

    const setPersistStoreValues = () => {
        if (!userId) {
            updateUserId(uuidV4());
        }
    };

    if (!isStoreSet.current) {
        setCookiesStoresValues();

        if (isClient) {
            setPersistStoreValues();
        }

        isStoreSet.current = true;
    }
};
