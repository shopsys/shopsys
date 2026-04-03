import { useRouter } from 'next/router';
import { startTransition, useEffect, useEffectEvent, useState } from 'react';

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

    const onComplete = useEffectEvent(() => {
        (callback ?? router.reload)();
    });

    useEffect(() => {
        const endTimeMs = parseDate(endTime);
        let isActive = true;
        let intervalId: ReturnType<typeof setInterval> | undefined;

        const tick = () => {
            if (!isActive) {
                return;
            }

            const durationMs = Math.max(0, endTimeMs - Date.now());

            if (durationMs <= 0) {
                isActive = false;
                if (intervalId) {
                    clearInterval(intervalId);
                }
                onComplete();
                return;
            }

            startTransition(() => {
                setTime({
                    ...calculateTimeLeft(durationMs),
                    isLoading: false,
                });
            });
        };

        const timeoutId = Number.isNaN(endTimeMs)
            ? undefined
            : setTimeout(() => {
                  tick();
                  if (isActive) {
                      intervalId = setInterval(tick, interval);
                  }
              }, 0);

        return () => {
            isActive = false;
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            if (intervalId) {
                clearInterval(intervalId);
            }
        };
    }, [endTime, interval]);

    return time;
};
