import dynamic from 'next/dynamic';
import { getPublicConfigProperty } from 'utils/config/getNextConfig';
import { isEnvironment } from 'utils/isEnvironment';

const showSymfonyToolbar = getPublicConfigProperty('showSymfonyToolbar', '');

const SymfonyDebugToolbar =
    isEnvironment('development') &&
    showSymfonyToolbar === '1' &&
    dynamic(
        () =>
            import('components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbar').then(
                (component) => component.SymfonyDebugToolbar,
            ),
        {
            ssr: true,
        },
    );

export const DeferredSymfonyDebugToolbar: FC = () => {
    return SymfonyDebugToolbar ? <SymfonyDebugToolbar /> : null;
};
