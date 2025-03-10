import { FunctionComponent, ReactNode, SVGProps } from 'react';

export type FunctionComponentProps = {
    className?: string;
    tid?: string;
    children?: ReactNode;
};

declare module 'react' {
    interface HTMLAttributes<T> extends DOMAttributes<T> {
        tid?: string;
    }
}

declare global {
    type FC<P = object> = FunctionComponent<P & FunctionComponentProps>;
    type SvgFC<P = object> = FC<P & SVGProps<SVGSVGElement>>;

    interface Window {
        Packeta: {
            Viewport: {
                element: null;
                originalValue: null;
                set: () => void;
                restore: () => void;
            };
            Util: {
                makeRequest: PacketeryMakeRequestFunction;
            };
            Widget: {
                baseUrl: string;
                healthUrl: string;
                versions: {
                    v5: 'v5';
                    v6: 'v6';
                };
                close: () => void;
                pick: PacketeryPickFunction;
            };
        };
        dataLayer: any[] | undefined;
    }

    const Loader: SeznamMapLoaderBase & {
        load: (key: string | null, config: SeznamMapAPILoaderConfig | null, onLoad: () => void) => void;
    };
}

export {};
