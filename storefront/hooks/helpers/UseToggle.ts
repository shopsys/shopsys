import { useState } from 'react';

/**
 * Hook for toggle boolean state
 * @param defaultValue for default bool setting
 */
export const useToggle = (defaultValue: boolean): [boolean, () => void] => {
    const [value, setValue] = useState(defaultValue);

    const toggleValue = () => setValue(!value);

    return [value, toggleValue];
};
