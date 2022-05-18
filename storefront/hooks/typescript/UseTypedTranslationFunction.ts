import { Translate } from 'next-translate';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';

export const useTypedTranslationFunction = (): Translate => {
    const { t } = useTranslation('common');
    const staticT = useRef<Translate>(t);

    return staticT.current;
};
