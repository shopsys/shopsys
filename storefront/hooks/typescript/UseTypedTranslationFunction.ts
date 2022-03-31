import { Translate } from 'next-translate';
import useTranslation from 'next-translate/useTranslation';

export const useTypedTranslationFunction = (): Translate => {
    const { t } = useTranslation('common');
    return t as Translate;
};
