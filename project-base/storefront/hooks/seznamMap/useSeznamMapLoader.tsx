import { logException } from 'helpers/errors/logException';
import { useCallback, useEffect, useState } from 'react';
import { SeznamMapAPILoaderConfig, SeznamMapLoaderConfig } from 'types/seznamMap';

export const useSeznamMapLoader = (
    loaderConfig?: SeznamMapLoaderConfig,
    loaderAPIConfig?: SeznamMapAPILoaderConfig,
    onError?: () => void,
): boolean => {
    const [isMapLoaded, setIsMapLoaded] = useState(false);

    const createLoaderScript = useCallback(() => {
        const script = document.createElement('script');
        script.src = 'https://api.mapy.cz/loader.js';
        script.async = true;

        script.onload = () => {
            if (typeof Loader !== 'object') {
                logException(new Error(`SMap Loader object was not initialized`));

                if (onError) {
                    onError();
                }

                return;
            }

            Loader.async = true;

            Loader.apiKey = loaderConfig?.apiKey ?? Loader.apiKey;
            Loader.lang = loaderConfig?.lang ?? Loader.lang;
            Loader.mode = loaderConfig?.mode ?? Loader.mode;

            Loader.load(null, loaderAPIConfig ?? null, () => {
                if (typeof SMap !== 'undefined') {
                    setIsMapLoaded(true);
                } else {
                    logException(new Error(`SMap was not initialized`));
                    if (onError) {
                        onError();
                    }
                }
            });
        };

        script.onerror = (error) => {
            if (typeof error === 'string') {
                logException(new Error(error));
            } else {
                logException(error);
            }

            if (onError) {
                onError();
            }
        };

        return script;
    }, [loaderAPIConfig, loaderConfig, onError]);

    useEffect(() => {
        let script: HTMLScriptElement | null = null;

        if (!isMapLoaded) {
            script = createLoaderScript();
            document.head.append(script);
        }

        return () => {
            if (script) {
                document.head.removeChild(script);
            }
        };
    }, [createLoaderScript, isMapLoaded]);

    return isMapLoaded;
};
