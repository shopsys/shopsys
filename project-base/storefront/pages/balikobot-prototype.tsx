import { Button } from 'components/Forms/Button/Button';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { NextPage } from 'next';
import { useEffect, useRef, useState } from 'react';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

// PROTOTYPE (SSP-4370) – only for checking how the Balíkobot pickup widget looks, not for production use

const BALIKOBOT_SDK_URL = 'https://dist.balikobot.com/widget/v1/balikobot-widget.js';
const BALIKOBOT_WIDGET_ID = '01a061d1-22b3-77e3-bfec-08510aa2f577';

type BalikobotPickupPoint = {
    branchUid: string;
    branchId: string;
    carrierCode: string;
    type: string;
    name: string;
    street: string;
    city: string;
    zip: string;
    country: string;
    latitude: number;
    longitude: number;
};

type BalikobotWidgetError = { code: string; message: string };

type BalikobotWidgetOptions = {
    widgetId: string;
    target?: string | HTMLElement;
    dismissible?: boolean;
    locale?: 'cs' | 'en' | 'sk';
    country?: string;
    cartTotal?: number;
    selectedBranchUid?: string;
    onSelect?: (point: BalikobotPickupPoint) => void;
    onClose?: () => void;
    onError?: (error: BalikobotWidgetError) => void;
};

type BalikobotWidgetInstance = { open: () => void; close: () => void };

declare global {
    interface Window {
        Balikobot?: { createPickupWidget: (options: BalikobotWidgetOptions) => BalikobotWidgetInstance };
    }
}

const loadBalikobotSdk = (): Promise<void> => {
    if (window.Balikobot) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = BALIKOBOT_SDK_URL;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Balíkobot SDK failed to load (check CSP script-src)'));
        document.head.appendChild(script);
    });
};

const BalikobotPrototypePage: NextPage<ServerSidePropsType> = () => {
    const { defaultLocale } = useDomainConfig();
    const [isSdkLoaded, setIsSdkLoaded] = useState(false);
    const [lastEvent, setLastEvent] = useState<{ name: string; payload: unknown } | null>(null);
    const [selectedPoint, setSelectedPoint] = useState<BalikobotPickupPoint | null>(null);
    const inlineWidgetRef = useRef<BalikobotWidgetInstance | null>(null);

    const locale = (['cs', 'en', 'sk'].includes(defaultLocale) ? defaultLocale : 'en') as 'cs' | 'en' | 'sk';

    const commonOptions: Omit<BalikobotWidgetOptions, 'target'> = {
        widgetId: BALIKOBOT_WIDGET_ID,
        country: 'CZ',
        locale,
        cartTotal: 1290,
        selectedBranchUid: selectedPoint?.branchUid,
        onSelect: (point) => {
            setSelectedPoint(point);
            setLastEvent({ name: 'onSelect', payload: point });
        },
        onClose: () => setLastEvent({ name: 'onClose', payload: null }),
        onError: (error) => setLastEvent({ name: 'onError', payload: error }),
    };

    useEffect(() => {
        loadBalikobotSdk()
            .then(() => setIsSdkLoaded(true))
            .catch((error: Error) => setLastEvent({ name: 'sdkLoadError', payload: error.message }));
    }, []);

    useEffect(() => {
        if (!isSdkLoaded || !window.Balikobot) {
            return undefined;
        }

        inlineWidgetRef.current = window.Balikobot.createPickupWidget({
            ...commonOptions,
            target: '#balikobot-inline-widget',
        });

        return () => inlineWidgetRef.current?.close();
        // the inline widget is created once, re-creating it on every selection would reset the map
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isSdkLoaded]);

    const openModal = () => window.Balikobot?.createPickupWidget(commonOptions).open();

    return (
        <CommonLayout title="Balíkobot prototype">
            <Webline className="flex flex-col gap-6 py-8">
                <h1>Balíkobot pickup widget – prototype</h1>

                <div>
                    <Button disabled={!isSdkLoaded} onClick={openModal}>
                        Open widget as modal
                    </Button>
                </div>

                <div>
                    <h2 className="mb-2">Last event</h2>
                    <pre className="overflow-auto rounded-md bg-background-more p-4 text-sm">
                        {lastEvent ? `${lastEvent.name}\n${JSON.stringify(lastEvent.payload, null, 2)}` : 'waiting…'}
                    </pre>
                </div>

                <div>
                    <h2 className="mb-2">Inline embed</h2>
                    <div className="h-[600px] w-full overflow-hidden rounded-md border" id="balikobot-inline-widget" />
                </div>
            </Webline>
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default BalikobotPrototypePage;
