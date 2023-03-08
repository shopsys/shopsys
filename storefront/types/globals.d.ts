import { FunctionComponent, ReactNode } from 'react';

declare global {
    type FC<P = object> = FunctionComponent<
        P & {
            className?: string;
            dataTestId?: string;
            children?: ReactNode;
        }
    >;
}

export {};
