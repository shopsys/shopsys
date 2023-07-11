import { useLoginLoader } from 'hooks/app/useLoginLoader';
import { usePageLoader } from 'hooks/app/usePageLoader';

export const LoadingHandler: FC = () => {
    useLoginLoader();
    usePageLoader();

    return null;
};
