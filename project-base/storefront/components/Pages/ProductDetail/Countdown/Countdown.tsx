import { SkeletonModuleCountdown } from 'components/Blocks/Skeleton/SkeletonModuleCountdown';
import dayjs, { Dayjs } from 'dayjs';
import duration from 'dayjs/plugin/duration';
import useTranslation from 'next-translate/useTranslation';
import React from 'react';
import { useCountdown } from 'utils/useCountdown';

dayjs.extend(duration);

type CountdownProps = {
    endTime: Dayjs;
};

export const Countdown: FC<CountdownProps> = ({ endTime }) => {
    const { t } = useTranslation();
    const countdown = useCountdown(endTime);

    if (countdown.isLoading) {
        return <SkeletonModuleCountdown />;
    }

    return (
        <div className="flex flex-col gap-2">
            <span className="h6">{t('Promo price expires in')}</span>
            <div className="flex items-center gap-2">
                <CountdownItem unit={t('days count', { count: Number(countdown.days) })} value={countdown.days} />
                <CountdownDivider />
                <CountdownItem unit={t('hrs count', { count: Number(countdown.hours) })} value={countdown.hours} />
                <CountdownDivider />
                <CountdownItem unit={t('mins count', { count: Number(countdown.minutes) })} value={countdown.minutes} />
                <CountdownDivider />
                <CountdownItem unit={t('secs')} value={countdown.seconds} />
            </div>
        </div>
    );
};

const CountdownItem: FC<{ value: string; unit: string }> = ({ value, unit }) => {
    return (
        <div className="bg-background-default min-w-14 rounded-lg p-2 text-center shadow-md md:min-w-16">
            <div className="text-xl font-bold md:text-2xl">{value}</div>
            <div className="text-xs uppercase">{unit}</div>
        </div>
    );
};

const CountdownDivider: FC = () => {
    return <span className="text-xl font-bold md:text-2xl">:</span>;
};
