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
            <div className="fixed bottom-2 left-2 rounded bg-backgroundError p-2 text-textInverted">
                Defer is turned off
            </div>
        </>
    );
};
