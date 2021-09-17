import { SearchFormStyled, SearchInStyled, SearchStyled } from './Search.style';
import { FC } from 'react';
import TextInput from '../../../Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Search: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <SearchStyled>
            <SearchInStyled>
                <SearchFormStyled>
                    <TextInput
                        type="search"
                        placeholderType="static"
                        inputSize="small"
                        id="search"
                        variant="searchInHeader"
                        name="search"
                        label={t("Type what you're looking for")}
                        style={{ width: '100%', marginBottom: '0' }}
                    />
                </SearchFormStyled>
            </SearchInStyled>
        </SearchStyled>
    );
};

/* @component */
export default Search;
