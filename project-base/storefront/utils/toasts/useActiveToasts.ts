import { useEffect, useState } from 'react';
import { toast, ToastItem } from 'react-toastify';

export const useActiveToasts = (): boolean => {
    const [activeToastCount, setActiveToastCount] = useState(0);

    useEffect(() => {
        const unsubscribe = toast.onChange((payload: ToastItem) => {
            switch (payload.status) {
                case 'added':
                    setActiveToastCount((prev) => prev + 1);
                    break;
                case 'removed':
                    setActiveToastCount((prev) => Math.max(0, prev - 1));
                    break;
                // 'updated' doesn't change the count
            }
        });

        return unsubscribe;
    }, []);

    return activeToastCount > 0;
};
