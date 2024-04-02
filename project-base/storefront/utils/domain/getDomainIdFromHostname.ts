const DOMAIN_COUNT = 2;

export const getDomainIdFromHostname = (hostname: string): number => {
    let currentDomainId = null as null | number;

    for (let index = 0; index < DOMAIN_COUNT; index++) {
        if (currentDomainId) {
            break;
        }

        const domainId = index + 1;
        const domainHostname = process.env[`DOMAIN_HOSTNAME_${domainId}`];
        if (domainHostname) {
            const { host } = new URL(domainHostname);

            if (hostname === host) {
                currentDomainId = domainId;
            }
        } else {
            throw new Error(`Error from getDomainIdFromHostname - Variable ${domainHostname} is undefined`);
        }
    }

    if (currentDomainId) {
        return currentDomainId;
    }

    throw new Error(`Cannot get domain id from hostname: ${hostname}`);
};
