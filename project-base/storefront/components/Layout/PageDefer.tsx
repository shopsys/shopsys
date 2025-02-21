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
            <div className="bg-backgroundError text-textInverted fixed bottom-2 left-2 rounded-sm p-2">
                Defer is turned off
            </div>
        </>
    );
};
