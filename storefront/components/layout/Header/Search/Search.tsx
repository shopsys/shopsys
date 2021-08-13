import { FormProvider, useForm } from 'react-hook-form';
import { SearchFormStyled, SearchInStyled, SearchStyled } from './Search.style';
import { ReactElement } from 'react';
import ShopsysTextInput from '../../../forms/ShopsysTextInput';
import { useTranslation } from 'react-i18next';

const Search = (): ReactElement => {
    const { t } = useTranslation();
    const formProviderMethods = useForm({
        mode: 'all',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
    });

    return (
        <SearchStyled>
            <SearchInStyled>
                <SearchFormStyled>
                    <FormProvider {...formProviderMethods}>
                        <ShopsysTextInput
                            placeholderType="static"
                            inputSize="small"
                            id="search"
                            variant="searchInHeader"
                            name="search"
                            label={t('Napište co hledáte')}
                            style={{ width: '100%', marginBottom: '0' }}
                        />
                    </FormProvider>
                </SearchFormStyled>
            </SearchInStyled>
        </SearchStyled>
    );
};

/* @component */
export default Search;
