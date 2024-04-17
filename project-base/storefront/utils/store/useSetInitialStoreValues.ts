import { useRef } from 'react';
import { CookiesStoreState } from 'store/createCookieStore';
import { useCookiesStore } from 'store/useCookiesStore';
import { usePersistStore } from 'store/usePersistStore';
import { isClient } from 'utils/isClient';
import { ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { v4 as uuidV4 } from 'uuid';

export const useSetInitialStoreValues = ({ cookiesStore }: ServerSidePropsType) => {
    const isStoreSet = useRef(false);

    const userIdentifier = usePersistStore((store) => store.userIdentifier);
    const updateUserIdentifier = usePersistStore((store) => store.updateUserIdentifier);
    const setCookieStoreValue = useCookiesStore((state) => state.setCookiesStoreState);

    const setCookiesStoresValues = () => {
        const cookieStore: CookiesStoreState = cookiesStore ? JSON.parse(cookiesStore) : {};

        setCookieStoreValue(cookieStore);
    };

    const setPersistStoreValues = () => {
        if (!userIdentifier) {
            updateUserIdentifier(uuidV4());
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
