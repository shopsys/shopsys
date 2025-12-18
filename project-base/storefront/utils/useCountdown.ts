import { useRouter } from 'next/router';
import { useState, useEffect, useCallback } from 'react';

interface TimeState {
    days: string;
    hours: string;
    minutes: string;
    seconds: string;
    isLoading: boolean;
}

type CountdownTime = string | Date;

const calculateTimeLeft = (durationMs: number): Omit<TimeState, 'isLoading'> => {
    const totalSeconds = Math.floor(durationMs / 1000);
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

const parseDate = (date: CountdownTime): number => {
    return new Date(date).getTime();
};

export const useCountdown = (endTime: CountdownTime, callback?: () => void, interval = 1000): TimeState => {
    const router = useRouter();

    const [time, setTime] = useState<TimeState>({
        days: '00',
        hours: '00',
        minutes: '00',
        seconds: '00',
        isLoading: true,
    });

    const effectiveCallback = useCallback(() => (callback ?? router.reload)(), [callback, router]);

    const updateTime = useCallback(
        (durationMs: number) => {
            if (durationMs <= 0) {
                effectiveCallback();
                return false;
            }

            setTime({
                ...calculateTimeLeft(durationMs),
                isLoading: false,
            });

            return true;
        },
        [effectiveCallback],
    );

    useEffect(() => {
        const currentTime = Date.now();
        const endTimeMs = parseDate(endTime);

        if (isNaN(endTimeMs)) {
            return undefined;
        }

        let durationMs = endTimeMs - currentTime;

        const intervalId = setInterval(() => {
            durationMs = durationMs - interval;

            if (!updateTime(durationMs)) {
                clearInterval(intervalId);
            }
        }, interval);

        return () => clearInterval(intervalId);
    }, [endTime, updateTime, interval]);

    return time;
};
