import { Autocomplete } from './Autocomplete/Autocomplete';
import {
    AutocompleteSearchInnerStyled,
    AutocompleteSearchInStyled,
    AutocompleteSearchRemoveButtonImageStyled,
    AutocompleteSearchRemoveButtonStyled,
    AutocompleteSearchRemoveButtonTextStyled,
    AutocompleteSearchStyled,
    AutocompleteSearchTextInputStyled,
} from './AutocompleteSearch.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { MINIMAL_SEARCH_QUERY_LENGTH, useAutocompleteSearch } from 'connectors/search/AutocompleteSearch';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useGtmSearchResultView } from 'hooks/gtm/useGtmSearchResultView';
import { useDebounce } from 'hooks/helpers/useDebounce';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useRouter } from 'next/router';
import { ChangeEventHandler, FC, useCallback, useMemo, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';

const TEST_IDENTIFIER = 'layout-header-search-autocomplete-input';

export const AutocompleteSearch: FC = () => {
    const router = useRouter();
    const [autocompleteSearchQueryValue, setAutocompleteSearchQueryValue] = useState('');
    const debouncedAutocompleteSearchQuery = useDebounce(autocompleteSearchQueryValue, 200);
    const [hasAutocompleteSearchFocus, setAutocompleteSearchFocus] = useState(false);
    const autocompleteSearchApiResults = useAutocompleteSearch(debouncedAutocompleteSearchQuery);
    const autocompleteSearchInRef = useRef<HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);
    const t = useTypedTranslationFunction();
    const [isDesktop, setIsDesktop] = useState(false);
    const { width } = useGetWindowSize();

    const autocompleteSearchResults = useMemo(() => {
        if (autocompleteSearchQueryValue.length < MINIMAL_SEARCH_QUERY_LENGTH) {
            return undefined;
        }

        return autocompleteSearchApiResults;
    }, [autocompleteSearchApiResults, autocompleteSearchQueryValue]);

    useGtmSearchResultView(autocompleteSearchApiResults, autocompleteSearchQueryValue);

    useEffectOnce(() => {
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

        document.addEventListener('click', onDocumentClickHandler);

        return () => document.removeEventListener('click', onDocumentClickHandler);
    });

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsDesktop(true),
        () => setIsDesktop(false),
    );

    const onAutocompleteSearchHandler = useCallback(() => {
        router.push({ pathname: searchUrl, query: { q: autocompleteSearchQueryValue } });
    }, [router, autocompleteSearchQueryValue, searchUrl]);

    const onChangeAutocompleteSearchQueryValueHandler: ChangeEventHandler<HTMLInputElement> = useCallback((event) => {
        setAutocompleteSearchQueryValue(event.currentTarget.value);
    }, []);

    return (
        <>
            <AutocompleteSearchStyled>
                <AutocompleteSearchInStyled ref={autocompleteSearchInRef}>
                    <AutocompleteSearchInnerStyled isActive={hasAutocompleteSearchFocus}>
                        <AutocompleteSearchTextInputStyled
                            type="search"
                            placeholderType="static"
                            inputSize="small"
                            variant="searchInHeader"
                            label={t("Type what you're looking for")}
                            testIdentifier={TEST_IDENTIFIER}
                            onEnterPressCallback={onAutocompleteSearchHandler}
                            value={autocompleteSearchQueryValue}
                            onChange={onChangeAutocompleteSearchQueryValueHandler}
                        />
                        {hasAutocompleteSearchFocus && autocompleteSearchQueryValue.length > 0 && (
                            <AutocompleteSearchRemoveButtonStyled onClick={() => setAutocompleteSearchQueryValue('')}>
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
                    </AutocompleteSearchInnerStyled>
                    <Autocomplete
                        autocompleteSearchResults={autocompleteSearchResults}
                        isAutocompleteActive={
                            hasAutocompleteSearchFocus &&
                            autocompleteSearchQueryValue.length >= MINIMAL_SEARCH_QUERY_LENGTH &&
                            autocompleteSearchResults !== undefined
                        }
                        autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                    />
                </AutocompleteSearchInStyled>
            </AutocompleteSearchStyled>
        </>
    );
};
