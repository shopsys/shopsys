import 'nprogress/nprogress.css';
import { usePageLoader } from 'utils/app/usePageLoader';
import { useAuthStateSynchronization } from 'utils/auth/useAuthStateSynchronization';

export const Loaders = () => {
    useAuthStateSynchronization();
    usePageLoader();

    return null;
};
