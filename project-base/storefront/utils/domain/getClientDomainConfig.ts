import { DomainConfig } from 'envConfig';
import { resolveDomainConfigByHost } from 'utils/domain/domainConfig';

export function getClientDomainConfig(): DomainConfig {
    const locale = document.documentElement.lang || undefined;
    const { domainConfig } = resolveDomainConfigByHost(window.location.host, locale);

    if (domainConfig) {
        return domainConfig;
    }

    if (locale) {
        const fallbackResult = resolveDomainConfigByHost(window.location.host);

        if (fallbackResult.domainConfig) {
            return fallbackResult.domainConfig;
        }
    }

    throw new Error(`Cannot resolve domainConfig on client for host '${window.location.host}'`);
}
