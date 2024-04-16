import getConfig from 'next/config';
import { Suspense } from 'react';

const {
    publicRuntimeConfig: { shouldUseDefer },
} = getConfig();

export const PageDefer: FC = ({ children }) => {
    if (shouldUseDefer) {
        return <Suspense>{children}</Suspense>;
    }

    return children;
};
