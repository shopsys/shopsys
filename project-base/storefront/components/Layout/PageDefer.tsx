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
            <div className="fixed left-2 bottom-2 p-2 bg-backgroundError text-textInverted rounded">
                Defer is turned off
            </div>
        </>
    );
};
