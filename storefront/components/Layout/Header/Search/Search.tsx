import { Controller, useWatch } from 'react-hook-form';
import { FC, useEffect, useRef, useState } from 'react';
import {
    RemoveSearchButtonStyled,
    SearchFormStyled,
    SearchInStyled,
    SearchStyled,
    SearchTextInputStyled,
} from './Search.style';
import { getSearch } from 'connectors/search/Search';
import Icon from 'components/Basic/Icon';
import { SearchType } from 'connectors/search/types';
import useDebounce from 'hooks/helpers/UseDebounce';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Search: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useShopsysForm(undefined, { searchQuery: '' });
    const searchQueryValue = useWatch({ name: 'searchQuery', control: formProviderMethods.control });
    const debouncedSearchQuery = useDebounce(searchQueryValue, 200);
    const [hasSearchFocus, setSearchFocus] = useState(false);
    const searchApiResults = getSearch(debouncedSearchQuery);
    const [searchResults, setSearchResults] = useState<SearchType | undefined>(undefined);
    const searchInRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (searchQueryValue.length < 3) {
            setSearchResults(undefined);
        } else {
            setSearchResults(searchApiResults);
        }
    }, [JSON.stringify(searchApiResults), searchQueryValue]);

    useEffect(() => {
        if (typeof document === 'undefined') {
            return;
        }

        document.addEventListener('click', onDocumentClickHandler);
    }, [typeof document]);

    useEffect(() => {
        return () => document.removeEventListener('click', onDocumentClickHandler);
    }, []);

    const onDocumentClickHandler: EventListener = (event) => {
        if (searchInRef.current === null || !(event.target instanceof HTMLElement)) {
            return;
        }

        if (searchInRef.current.contains(event.target)) {
            setSearchFocus(true);
        } else {
            setSearchFocus(false);
        }
    };

    return (
        <>
            <SearchStyled>
                <SearchInStyled ref={searchInRef}>
                    <SearchFormStyled isActive={hasSearchFocus}>
                        <Controller
                            control={formProviderMethods.control}
                            name="searchQuery"
                            render={({ field }) => (
                                <SearchTextInputStyled
                                    type="search"
                                    placeholderType="static"
                                    inputSize="small"
                                    id="search"
                                    variant="searchInHeader"
                                    name="search"
                                    label={t("Type what you're looking for")}
                                    fieldRef={field}
                                />
                            )}
                        />
                        {hasSearchFocus && searchQueryValue.length > 0 && (
                            <RemoveSearchButtonStyled onClick={() => formProviderMethods.setValue('searchQuery', '')}>
                                <Icon icon="Remove" />
                            </RemoveSearchButtonStyled>
                        )}
                    </SearchFormStyled>
                </SearchInStyled>
            </SearchStyled>
        </>
    );
};

/* @component */
export default Search;
