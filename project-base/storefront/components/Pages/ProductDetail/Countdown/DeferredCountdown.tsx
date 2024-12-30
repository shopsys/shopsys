import { SkeletonModuleCountdown } from 'components/Blocks/Skeleton/SkeletonModuleCountdown';
import { Dayjs } from 'dayjs';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const Countdown = dynamic(() => import('./Countdown').then((component) => component.Countdown), {
    ssr: false,
    loading: () => <SkeletonModuleCountdown />,
});

type DeferredCountdownProps = {
    endTime: Dayjs;
};

export const DeferredCountdown: FC<DeferredCountdownProps> = ({ endTime }) => {
    const shouldRender = useDeferredRender('countdown');

    return shouldRender ? <Countdown endTime={endTime} /> : <SkeletonModuleCountdown />;
};
