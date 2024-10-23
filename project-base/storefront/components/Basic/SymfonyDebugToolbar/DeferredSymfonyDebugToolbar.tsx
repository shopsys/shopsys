import getConfig from 'next/config';
import dynamic from 'next/dynamic';
import { isEnvironment } from 'utils/isEnvironment';

const {
    publicRuntimeConfig: { showSymfonyToolbar },
} = getConfig();

const SymfonyDebugToolbar =
    isEnvironment('development') &&
    showSymfonyToolbar === '1' &&
    dynamic(
        () =>
            import('components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbar').then(
                (component) => ({
                    default: component.SymfonyDebugToolbar
                }),
            ),
        {
            ssr: true,
        },
    );

export const DeferredSymfonyDebugToolbar: FC = () => {
    return SymfonyDebugToolbar ? <SymfonyDebugToolbar /> : null;
};
