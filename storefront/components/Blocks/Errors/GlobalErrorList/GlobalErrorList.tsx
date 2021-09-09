import { FC, useContext } from 'react';
import { ShopsysGlobalErrorContext } from '../../../../context/ShopsysGlobalErrorProvider/ShopsysGlobalErrorProvider';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

export const GlobalErrorList: FC = () => {
    const t = useTypedTranslationFunction();
    const { errors } = useContext(ShopsysGlobalErrorContext);

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

export default GlobalErrorList;
