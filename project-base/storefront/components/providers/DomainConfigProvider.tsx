import { DomainConfigType } from 'helpers/domain/domainConfig';
import { createContext, useContext } from 'react';

export const DomainConfigContext = createContext<DomainConfigType | null>(null);

type DomainConfigProviderProps = {
    domainConfig: DomainConfigType;
};

export const DomainConfigProvider: FC<DomainConfigProviderProps> = ({ domainConfig, children }) => {
    return <DomainConfigContext.Provider value={domainConfig}>{children}</DomainConfigContext.Provider>;
};

export const useDomainConfig = () => {
    const domainConfigContext = useContext(DomainConfigContext);

    if (!domainConfigContext) {
        throw new Error(`useDomainConfig must be use within DomainConfigProvider`);
    }

    return domainConfigContext;
};
