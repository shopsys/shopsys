import { Translate } from 'next-translate';
import { useRef } from 'react';
import useTranslation from 'next-translate/useTranslation';

export const useTypedTranslationFunction = (): Translate => {
    const { t } = useTranslation('common');
    const staticT = useRef<Translate>(t);

    return staticT.current;
};
