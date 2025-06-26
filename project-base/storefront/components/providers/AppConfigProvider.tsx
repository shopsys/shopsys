'use client';

import { TypeSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.ssr';
import { createContext, useContext } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';

export type AppConfig = {
    settings: Exclude<TypeSettingsQuery['settings'], null>;
    domainConfig: DomainConfigType;
    staticRewritePaths: Record<string, string>;
};
export const AppConfigContext = createContext<AppConfig | null>(null);

type AppConfigProviderProps = {
    settings: TypeSettingsQuery['settings'] | undefined;
    domainConfig: DomainConfigType;
    staticRewritePaths: Record<string, string>;
};

export const AppConfigProvider: FC<AppConfigProviderProps> = ({
    settings,
    domainConfig,
    staticRewritePaths,
    children,
}) => {
    if (!settings) {
        throw new Error('Failed to fetch settings');
    }

    return (
        <AppConfigContext.Provider
            value={{
                settings,
                domainConfig,
                staticRewritePaths,
            }}
        >
            {children}
        </AppConfigContext.Provider>
    );
};

export function useAppConfig<T = AppConfig>(selector?: (settings: AppConfig) => T): T {
    const appConfigData = useContext(AppConfigContext);

    if (!appConfigData) {
        throw new Error(`useAppConfig must be used within AppConfigProvider`);
    }

    const selectedData = selector ? selector(appConfigData) : appConfigData;

    if (typeof selectedData === 'undefined' || selectedData === null) {
        throw new Error('useSettings selector returned undefined');
    }

    return (selector ? selector(appConfigData) : appConfigData) as T;
}
