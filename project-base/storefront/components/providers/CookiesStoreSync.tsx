'use client';

import { useCookiesStoreSync } from 'utils/cookies/cookiesStore';

export const CookiesStoreSync: FC = () => {
    useCookiesStoreSync();

    return null;
};

export default CookiesStoreSync;
