import { DomainConfigType } from 'helpers/domain/domain';
import { StateCreator } from 'zustand';

export type DomainSlice = {
    domainConfig: DomainConfigType;
    setDomainConfig: (value: DomainConfigType) => void;
};

export const createDomainSlice: StateCreator<DomainSlice> = (set) => ({
    domainConfig: {
        url: process.env.DOMAIN_HOSTNAME_1!,
        publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1!,
        defaultLocale: 'cs',
        currencyCode: 'CZK',
        domainId: 1,
        mapSetting: {
            latitude: 49.8175,
            longitude: 15.473,
            zoom: 7,
        },
    },

    setDomainConfig: (value: DomainConfigType) => {
        set({ domainConfig: value });
    },
});
