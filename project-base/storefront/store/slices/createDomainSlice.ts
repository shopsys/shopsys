import { DomainConfigType } from 'helpers/domain/domainConfig';
import { StateCreator } from 'zustand';

export type DomainSlice = {
    domainConfig: DomainConfigType | undefined;
    setDomainConfig: (value: DomainConfigType) => void;
};

export const createDomainSlice: StateCreator<DomainSlice> = (set) => ({
    domainConfig: undefined,

    setDomainConfig: (value: DomainConfigType) => {
        set({ domainConfig: value });
    },
});
