import { DomainConfigType } from 'helpers/domain/domainConfig';
import { logException } from 'helpers/errors/logException';
import { useSessionStore } from 'store/useSessionStore';

export const useDomainConfig = (): DomainConfigType => {
    const domainConfig = useSessionStore((state) => state.domainConfig);

    if (!domainConfig) {
        logException('Domain config was undefined.');
    }

    return domainConfig!;
};
