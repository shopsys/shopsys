import { EffectCallback, useEffect } from 'react';

export const useEffectOnce = (effect: EffectCallback): void => {
    useEffect(effect, []);
};
