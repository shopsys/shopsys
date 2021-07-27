import { SsfwGlobalErrorContext } from '../../../SsfwGlobalErrorProvider/SsfwGlobalErrorProvider';
import { useContext } from 'react';
import { useTranslation } from 'react-i18next';

export const GlobalErrorList = () => {
    const { t } = useTranslation();
    const { state } = useContext(SsfwGlobalErrorContext);
    const errors = state;

    if (errors && errors.length > 0) {
        return (
            <>
                {t('Oh no! Errors:')}
                <ul>
                    {errors.map((message, key) => (
                        <li key={key}>{message}</li>
                    ))}
                </ul>
            </>
        );
    }

    return null;
};
