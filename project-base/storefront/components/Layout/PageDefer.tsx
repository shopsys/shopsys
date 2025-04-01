import getConfig from 'next/config';
import { Suspense } from 'react';

const {
    publicRuntimeConfig: { shouldUseDefer },
} = getConfig();

export const PageDefer: FC = ({ children }) => {
    if (shouldUseDefer) {
        return <Suspense>{children}</Suspense>;
    }

    return (
        <>
            {children}
            <div className="bg-backgroundError text-text-inverted z-maximum fixed bottom-5 left-16 rounded-sm p-2 text-sm">
                Defer is turned off
            </div>
        </>
    );
};
