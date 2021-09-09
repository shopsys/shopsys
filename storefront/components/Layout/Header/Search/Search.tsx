import { FormProvider, useForm } from 'react-hook-form';
import { SearchFormStyled, SearchInStyled, SearchStyled } from './Search.style';
import { ReactElement } from 'react';
import ShopsysTextInput from '../../../Forms/ShopsysTextInput';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const Search = (): ReactElement => {
    const t = useTypedTranslationFunction();
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
                            label={t("Type what you're looking for")}
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
