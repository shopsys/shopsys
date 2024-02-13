import { DomainConfigType } from 'helpers/domain/domainConfig';
import { logException } from 'helpers/errors/logException';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const useDomainConfig = (): DomainConfigType => {
    const domainConfig = useSessionStore((state) => state.domainConfig);

    if (!domainConfig) {
        logException('Domain config was undefined.');
    }

    return domainConfig!;
};

export const useSetDomainConfig = (initialDomainConfig: DomainConfigType) => {
    const [isConfigSet, setIsConfigSet] = useState(false);

    const setDomainConfig = useSessionStore((state) => state.setDomainConfig);

    /**
     * React complains about too many rerenders, probably because it is setting twice
     * and because it needs to run also on SSR we cannot use useEffect here
     */
    if (!isConfigSet) {
        setDomainConfig(initialDomainConfig);
        setIsConfigSet(true);
    }
};
