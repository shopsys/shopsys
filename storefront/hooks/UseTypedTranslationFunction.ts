import { TFunction, useTranslation } from 'react-i18next';

export const useTypedTranslationFunction = (): TFunction<string> => {
    const { t } = useTranslation();
    return t as TFunction<string>;
};
