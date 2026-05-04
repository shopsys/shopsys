import { useRouter } from 'next/router';
import { createContext, useContext, useEffect, useRef, useState } from 'react';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

type GtmContextType = {
    didPageReadyRun: boolean;
    setDidPageReadyRun: (newState: boolean) => void;
    isScriptLoaded: boolean;
    setIsScriptLoaded: (newState: boolean) => void;
    ipAddress?: string;
};

const GtmContext = createContext<GtmContextType | null>(null);

type GtmProviderProps = {
    ipAddress?: string;
};

export const GtmProvider: FC<GtmProviderProps> = ({ children, ipAddress }) => {
    const [didPageReadyRun, setDidPageReadyRun] = useState(false);
    const [isScriptLoaded, setIsScriptLoaded] = useState(false);
    const router = useRouter();
    const currentPathRef = useRef(getUrlWithoutGetParameters(router.asPath));

    useEffect(() => {
        const onRouteChangeStart = (url: string) => {
            const newPath = getUrlWithoutGetParameters(url);
            if (currentPathRef.current !== newPath) {
                currentPathRef.current = newPath;
                setDidPageReadyRun(false);
            }
        };

        router.events.on('routeChangeStart', onRouteChangeStart);

        return () => {
            router.events.off('routeChangeStart', onRouteChangeStart);
        };
    }, [router.events]);

    return (
        <GtmContext.Provider
            value={{ didPageReadyRun, setDidPageReadyRun, isScriptLoaded, setIsScriptLoaded, ipAddress }}
        >
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
