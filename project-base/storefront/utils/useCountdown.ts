import dayjs, { Dayjs } from 'dayjs';
import duration from 'dayjs/plugin/duration';
import { useRouter } from 'next/router';
import { useState, useEffect, useCallback } from 'react';

dayjs.extend(duration);

interface TimeState {
    days: string;
    hours: string;
    minutes: string;
    seconds: string;
    isLoading: boolean;
}

type CountdownTime = Dayjs | string | Date;

const calculateTimeLeft = (duration: duration.Duration): Omit<TimeState, 'isLoading'> => {
    const totalMilliseconds = duration.asMilliseconds();
    const totalSeconds = Math.floor(totalMilliseconds / 1000);
    const totalMinutes = Math.floor(totalSeconds / 60);
    const totalHours = Math.floor(totalMinutes / 60);
    const totalDays = Math.floor(totalHours / 24);

    const twoDigits = (n: number) => n.toString().padStart(2, '0');

    return {
        days: twoDigits(totalDays),
        hours: twoDigits(totalHours % 24),
        minutes: twoDigits(totalMinutes % 60),
        seconds: twoDigits(totalSeconds % 60),
    };
};

export const useCountdown = (
    endTime: CountdownTime,
    callback: () => void = () => router.reload(),
    interval = 1000,
): TimeState => {
    const router = useRouter();

    const [time, setTime] = useState<TimeState>({
        days: '00',
        hours: '00',
        minutes: '00',
        seconds: '00',
        isLoading: true,
    });

    const updateTime = useCallback(
        (duration: duration.Duration) => {
            if (duration.asMilliseconds() <= 0) {
                callback();
                return false;
            }

            setTime(() => ({
                ...calculateTimeLeft(duration),
                isLoading: false,
            }));

            return true;
        },
        [router],
    );

    useEffect(() => {
        const currentTime = dayjs();
        const endTimeDayjs = dayjs(endTime);

        if (!endTimeDayjs.isValid()) {
            return;
        }

        const diffTime = endTimeDayjs.diff(currentTime);
        let duration = dayjs.duration(diffTime);

        const intervalId = setInterval(() => {
            duration = duration.subtract(interval);

            if (!updateTime(duration)) {
                clearInterval(intervalId);
            }
        }, interval);

        // eslint-disable-next-line consistent-return
        return () => clearInterval(intervalId);
    }, [endTime, updateTime]);

    return time;
};
