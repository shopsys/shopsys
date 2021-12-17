import {
    AutocompleteSearchFormStyled,
    AutocompleteSearchInStyled,
    AutocompleteSearchRemoveButtonImageStyled,
    AutocompleteSearchRemoveButtonStyled,
    AutocompleteSearchRemoveButtonTextStyled,
    AutocompleteSearchStyled,
    AutocompleteSearchTextInputStyled,
} from './AutocompleteSearch.style';
import { Controller, FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { FC, useEffect, useRef, useState } from 'react';
import { useAutocompleteSearchForm, useAutocompleteSearchFormMeta } from './formMeta';
import Autocomplete from './Autocomplete';
import { AutocompleteSearchFormType } from 'types/form';
import { AutocompleteSearchType } from 'types/search';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { getAutocompleteSearch } from 'connectors/search/AutocompleteSearch';
import Icon from 'components/Basic/Icon';
import useDebounce from 'hooks/helpers/UseDebounce';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const AutocompleteSearch: FC = () => {
    const router = useRouter();
    const [formProviderMethods] = useAutocompleteSearchForm();
    const formMeta = useAutocompleteSearchFormMeta(formProviderMethods);
    const autocompleteSearchQueryValue = useWatch({
        name: formMeta.fields.autocompleteSearchQuery.name,
        control: formProviderMethods.control,
    });
    const debouncedAutocompleteSearchQuery = useDebounce(autocompleteSearchQueryValue, 200);
    const [hasAutocompleteSearchFocus, setAutocompleteSearchFocus] = useState(false);
    const autocompleteSearchApiResults = getAutocompleteSearch(debouncedAutocompleteSearchQuery);
    const [autocompleteSearchResults, setAutocompleteSearchResults] = useState<AutocompleteSearchType | undefined>(
        undefined,
    );
    const autocompleteSearchInRef = useRef<HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = useGetInternationalizedStaticUrls(['/search'], domainUrl);
    const t = useTypedTranslationFunction();
    const [isDesktop, setIsDesktop] = useState(false);
    const { width } = useGetWindowSize();

    useEffect(() => {
        if (formProviderMethods.formState.isValid) {
            setAutocompleteSearchResults(autocompleteSearchApiResults);
        } else {
            setAutocompleteSearchResults(undefined);
        }
    }, [JSON.stringify(autocompleteSearchApiResults), autocompleteSearchQueryValue]);

    useEffect(() => {
        if (typeof document === 'undefined') {
            return;
        }

        document.addEventListener('click', onDocumentClickHandler);
    }, [typeof document]);

    useEffect(() => {
        return () => document.removeEventListener('click', onDocumentClickHandler);
    }, []);

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsDesktop(true),
        () => setIsDesktop(false),
    );

    const onDocumentClickHandler: EventListener = (event) => {
        if (autocompleteSearchInRef.current === null || !(event.target instanceof HTMLElement)) {
            setAutocompleteSearchFocus(false);
            return;
        }

        if (autocompleteSearchInRef.current.contains(event.target)) {
            setAutocompleteSearchFocus(true);
        } else {
            setAutocompleteSearchFocus(false);
        }
    };

    const onAutocompleteSearchSubmitHandler: SubmitHandler<AutocompleteSearchFormType> = (_data, event) => {
        event?.preventDefault();
        router.push({ pathname: searchUrl, query: { q: autocompleteSearchQueryValue } });
    };

    return (
        <>
            <AutocompleteSearchStyled>
                <AutocompleteSearchInStyled ref={autocompleteSearchInRef}>
                    <AutocompleteSearchFormStyled
                        isActive={hasAutocompleteSearchFocus}
                        onSubmit={formProviderMethods.handleSubmit(onAutocompleteSearchSubmitHandler)}
                    >
                        <FormProvider {...formProviderMethods}>
                            <Controller
                                control={formProviderMethods.control}
                                name={formMeta.fields.autocompleteSearchQuery.name}
                                render={({ field }) => (
                                    <AutocompleteSearchTextInputStyled
                                        id={formMeta.formName + '-' + formMeta.fields.autocompleteSearchQuery.name}
                                        name={formMeta.fields.autocompleteSearchQuery.name}
                                        type="search"
                                        placeholderType="static"
                                        inputSize="small"
                                        variant="searchInHeader"
                                        label={formMeta.fields.autocompleteSearchQuery.label}
                                        fieldRef={field}
                                    />
                                )}
                            />
                            {hasAutocompleteSearchFocus && autocompleteSearchQueryValue.length > 0 && (
                                <AutocompleteSearchRemoveButtonStyled
                                    onClick={() =>
                                        formProviderMethods.setValue(formMeta.fields.autocompleteSearchQuery.name, '')
                                    }
                                >
                                    {isDesktop ? (
                                        <Icon iconType="icon" icon="Close" />
                                    ) : (
                                        <>
                                            <AutocompleteSearchRemoveButtonImageStyled>
                                                <Icon iconType="icon" icon="Close" />
                                            </AutocompleteSearchRemoveButtonImageStyled>
                                            <AutocompleteSearchRemoveButtonTextStyled>
                                                {t('Close')}
                                            </AutocompleteSearchRemoveButtonTextStyled>
                                        </>
                                    )}
                                </AutocompleteSearchRemoveButtonStyled>
                            )}
                        </FormProvider>
                    </AutocompleteSearchFormStyled>
                    <Autocomplete
                        autocompleteSearchResults={autocompleteSearchResults}
                        isAutocompleteActive={
                            hasAutocompleteSearchFocus &&
                            autocompleteSearchQueryValue.length > 2 &&
                            autocompleteSearchResults !== undefined
                        }
                        autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                    />
                </AutocompleteSearchInStyled>
            </AutocompleteSearchStyled>
        </>
    );
};

/* @component */
export default AutocompleteSearch;
