'use client';

import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT, MINIMAL_SEARCH_QUERY_LENGTH } from './constants';
import { getAutocompleteSearchQuery } from 'app/_queries/getAutocompleteSearchQuery';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { AnimatePresence } from 'framer-motion';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { TypeAutocompleteSearchQueryVariables } from 'graphql/requests/search/queries/AutocompleteSearchQuery.ssr';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useDebounce } from 'utils/useDebounce';

const AutocompleteSearchPopup = dynamic(() =>
    import('./AutocompleteSearchPopup').then((component) => component.AutocompleteSearchPopup),
);

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

export const AutocompleteSearch: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const [loading, setLoading] = useState(false);
    const [isSearchResultsPopupOpen, setIsSearchResultsPopupOpen] = useState(false);
    const [searchData, setSearchData] = useState<TypeAutocompleteSearchQuery>();
    const [searchQueryValue, setSearchQueryValue] = useState('');

    const userIdentifier = useCookiesStore((store) => store.userIdentifier);

    const debouncedSearchQuery = useDebounce(searchQueryValue, 200);
    const isWithValidSearchQuery = searchQueryValue.length >= MINIMAL_SEARCH_QUERY_LENGTH;

    const autocompleteSearchAction = async () => {
        if (debouncedSearchQuery.length < MINIMAL_SEARCH_QUERY_LENGTH) {
            return;
        }

        setLoading(true);

        const variables: TypeAutocompleteSearchQueryVariables = {
            search: debouncedSearchQuery,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
            isAutocomplete: true,
            userIdentifier,
        };
        const result = await getAutocompleteSearchQuery(variables);

        setSearchData(result);
        setLoading(false);
    };

    useEffect(() => {
        if (!isWithValidSearchQuery) {
            setSearchData(undefined);
        }
    }, [searchQueryValue]);

    useEffect(() => {
        if (isWithValidSearchQuery) {
            autocompleteSearchAction();
        }
    }, [debouncedSearchQuery]);

    const isSearchResultsPopupVisible = isSearchResultsPopupOpen && isWithValidSearchQuery && (!!searchData || loading);

    const handleSearch = () => {
        if (isWithValidSearchQuery) {
            const params = new URLSearchParams('');
            params.set('q', searchQueryValue);
            router.push(`${searchUrl}?${params.toString()}`);

            setIsSearchResultsPopupOpen(false);
        }
    };

    // TODO: add gtm event
    // useGtmAutocompleteResultsViewEvent(searchData, debouncedSearchQuery);

    return (
        <>
            <div
                className={twJoin('relative flex w-full transition-all', isWithValidSearchQuery && 'z-aboveOverlay')}
                onFocus={() => setIsSearchResultsPopupOpen(true)}
            >
                <SearchInput
                    className="w-full"
                    label={t('Write what you are looking for...')}
                    shouldShowSpinnerInInput={loading}
                    value={searchQueryValue}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
                    onSearch={handleSearch}
                />

                <AnimatePresence>
                    {isSearchResultsPopupVisible && (
                        <AutocompleteSearchPopup
                            areAutocompleteSearchDataFetching={loading}
                            autocompleteSearchQueryValue={searchQueryValue}
                            autocompleteSearchResults={searchData}
                            onClosePopupCallback={() => setIsSearchResultsPopupOpen(false)}
                        />
                    )}
                </AnimatePresence>
            </div>

            <Overlay isActive={isSearchResultsPopupVisible} onClick={() => setIsSearchResultsPopupOpen(false)} />
        </>
    );
};
