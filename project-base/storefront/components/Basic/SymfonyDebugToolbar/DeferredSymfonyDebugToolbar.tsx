import { getPublicConfigProperty } from 'envConfig';
import dynamic from 'next/dynamic';
import { isEnvironment } from 'utils/isEnvironment';

const showSymfonyToolbar = getPublicConfigProperty('showSymfonyToolbar');

const SymfonyDebugToolbar =
    isEnvironment('development') &&
    showSymfonyToolbar === '1' &&
    dynamic(
        () =>
            import('components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbar').then(
                (component) => component.SymfonyDebugToolbar,
            ),
        {
            ssr: false,
        },
    );

export const DeferredSymfonyDebugToolbar: FC = () => {
    return SymfonyDebugToolbar ? <SymfonyDebugToolbar /> : null;
};
