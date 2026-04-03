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
            <div className="fixed bottom-5 left-16 z-maximum rounded-sm bg-background-error p-2 text-sm text-text-inverted">
                Defer is turned off
            </div>
        </>
    );
};
