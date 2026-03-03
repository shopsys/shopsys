import { getPublicConfigProperty } from 'envConfig';
import { Suspense } from 'react';

const shouldUseDefer = getPublicConfigProperty('shouldUseDefer');

export const PageDefer: FC = ({ children }) => {
    if (shouldUseDefer) {
        return <Suspense>{children}</Suspense>;
    }

    return (
        <>
            {children}
            <div className="bg-background-error text-text-inverted z-maximum fixed bottom-5 left-16 rounded-sm p-2 text-sm">
                Defer is turned off
            </div>
        </>
    );
};
