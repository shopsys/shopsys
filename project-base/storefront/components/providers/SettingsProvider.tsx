'use client';

import { TypeSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.ssr';
import { createContext, useContext } from 'react';

export const SettingsContext = createContext<TypeSettingsQuery | undefined>(undefined);

type SettingsProviderProps = {
    settings: TypeSettingsQuery | undefined;
};

export const SettingsProvider: FC<SettingsProviderProps> = ({ settings, children }) => {
    return <SettingsContext.Provider value={settings}>{children}</SettingsContext.Provider>;
};

export const useSettings = () => {
    const settingsData = useContext(SettingsContext);

    if (!settingsData?.settings) {
        throw new Error(`useSettings must be use within SettingsProvider`);
    }

    return settingsData.settings;
};
