import { useRouter } from 'next/router';
import React, { useState, createContext, useEffect } from 'react';
import { useContext } from 'react';

export type GtmContextType = {
    didPageViewRun: boolean;
    setDidPageViewRun: (newState: boolean) => void;
    isScriptLoaded: boolean;
    setIsScriptLoaded: (newState: boolean) => void;
};

export const GtmContext = createContext<GtmContextType | null>(null);

export const GtmProvider: FC = ({ children }) => {
    const [didPageViewRun, setDidPageViewRun] = useState(false);
    const [isScriptLoaded, setIsScriptLoaded] = useState(false);
    const router = useRouter();

    useEffect(() => {
        const onRouteChangeStart = () => {
            setDidPageViewRun(false);
        };

        router.events.on('routeChangeStart', onRouteChangeStart);

        return () => {
            router.events.off('routeChangeStart', onRouteChangeStart);
        };
    }, [router.events]);

    return (
        <GtmContext.Provider value={{ didPageViewRun, setDidPageViewRun, isScriptLoaded, setIsScriptLoaded }}>
            {children}
        </GtmContext.Provider>
    );
};

export const useGtmContext = (): GtmContextType => {
    const context = useContext(GtmContext);

    if (!context) {
        throw new Error('useGtmContext must be used within a GtmProvider');
    }

    return context;
};
