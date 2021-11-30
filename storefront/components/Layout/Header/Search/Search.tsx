import { Controller, FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { FC, useEffect, useRef, useState } from 'react';
import {
    RemoveSearchButtonStyled,
    SearchFormStyled,
    SearchInStyled,
    SearchStyled,
    SearchTextInputStyled,
} from './Search.style';
import { SearchFormType, useSearchForm, useSearchFormMeta } from './formMeta';
import Autocomplete from './Autocomplete';
import { getSearch } from 'connectors/search/Search';
import Icon from 'components/Basic/Icon';
import { SearchType } from 'connectors/search/types';
import useDebounce from 'hooks/helpers/UseDebounce';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';

const Search: FC = () => {
    const router = useRouter();
    const [formProviderMethods] = useSearchForm();
    const formMeta = useSearchFormMeta(formProviderMethods);
    const searchQueryValue = useWatch({ name: formMeta.fields.searchQuery.name, control: formProviderMethods.control });
    const debouncedSearchQuery = useDebounce(searchQueryValue, 200);
    const [hasSearchFocus, setSearchFocus] = useState(false);
    const searchApiResults = getSearch(debouncedSearchQuery);
    const [searchResults, setSearchResults] = useState<SearchType | undefined>(undefined);
    const searchInRef = useRef<HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = useGetInternationalizedStaticUrls(['/search'], domainUrl);

    useEffect(() => {
        if (formProviderMethods.formState.isValid) {
            setSearchResults(searchApiResults);
        } else {
            setSearchResults(undefined);
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
            setSearchFocus(false);
            return;
        }

        if (searchInRef.current.contains(event.target)) {
            setSearchFocus(true);
        } else {
            setSearchFocus(false);
        }
    };

    const onSearchSubmitHandler: SubmitHandler<SearchFormType> = (_data, event) => {
        event?.preventDefault();
        router.push({ pathname: searchUrl, query: { q: searchQueryValue } });
    };

    return (
        <>
            <SearchStyled>
                <SearchInStyled ref={searchInRef}>
                    <SearchFormStyled
                        isActive={hasSearchFocus}
                        onSubmit={formProviderMethods.handleSubmit(onSearchSubmitHandler)}
                    >
                        <FormProvider {...formProviderMethods}>
                            <Controller
                                control={formProviderMethods.control}
                                name={formMeta.fields.searchQuery.name}
                                render={({ field }) => (
                                    <SearchTextInputStyled
                                        id={formMeta.formName + '-' + formMeta.fields.searchQuery.name}
                                        name={formMeta.fields.searchQuery.name}
                                        type="search"
                                        placeholderType="static"
                                        inputSize="small"
                                        variant="searchInHeader"
                                        label={formMeta.fields.searchQuery.label}
                                        fieldRef={field}
                                        isSearchButtonDisabled={searchResults === undefined}
                                    />
                                )}
                            />
                            {hasSearchFocus && searchQueryValue.length > 0 && (
                                <RemoveSearchButtonStyled
                                    onClick={() => formProviderMethods.setValue(formMeta.fields.searchQuery.name, '')}
                                >
                                    <Icon iconType="icon" icon="Remove" />
                                </RemoveSearchButtonStyled>
                            )}
                        </FormProvider>
                    </SearchFormStyled>
                    <Autocomplete
                        searchResults={searchResults}
                        isAutocompleteActive={
                            hasSearchFocus && searchQueryValue.length > 2 && searchResults !== undefined
                        }
                        searchQueryValue={searchQueryValue}
                    />
                </SearchInStyled>
            </SearchStyled>
        </>
    );
};

/* @component */
export default Search;
