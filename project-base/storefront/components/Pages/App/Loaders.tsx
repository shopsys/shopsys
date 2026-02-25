import { useAuthLoader } from 'utils/app/useAuthLoader';
import { usePageLoader } from 'utils/app/usePageLoader';

export const Loaders = () => {
    useAuthLoader();
    usePageLoader();

    return null;
};
