import { useRouter } from 'next/router';
import React, { useState, createContext, useEffect } from 'react';

export type GtmContextType = {
    didPageViewRun: boolean;
    setDidPageViewRun: (newState: boolean) => void;
    isScriptLoaded: boolean;
    setIsScriptLoaded: (newState: boolean) => void;
};

const defaultState: GtmContextType = {
    didPageViewRun: false,
    setDidPageViewRun: () => undefined,
    isScriptLoaded: false,
    setIsScriptLoaded: () => undefined,
};

export const GtmContext = createContext(defaultState);

export const GtmProvider: FC = ({ children }) => {
    const [didPageViewRun, setDidPageViewRun] = useState(defaultState.didPageViewRun);
    const [isScriptLoaded, setIsScriptLoaded] = useState(defaultState.isScriptLoaded);
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
