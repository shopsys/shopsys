import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT, MINIMAL_SEARCH_QUERY_LENGTH } from './constants';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { AnimatePresence } from 'framer-motion';
import {
    TypeAutocompleteSearchQuery,
    useAutocompleteSearchQuery,
} from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useGtmAutocompleteResultsViewEvent } from 'gtm/utils/pageViewEvents/useGtmAutocompleteResultsViewEvent';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useDebounce } from 'utils/useDebounce';

const AutocompleteSearchPopup = dynamic(() =>
    import('./AutocompleteSearchPopup').then((component) => ({
        default: component.AutocompleteSearchPopup
    })),
);

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => ({
    default: component.Overlay
})));

export const AutocompleteSearch: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const [isSearchResultsPopupOpen, setIsSearchResultsPopupOpen] = useState(false);
    const [searchData, setSearchData] = useState<TypeAutocompleteSearchQuery>();
    const [searchQueryValue, setSearchQueryValue] = useState('');

    const userIdentifier = useCookiesStore((store) => store.userIdentifier);

    const debouncedSearchQuery = useDebounce(searchQueryValue, 200);
    const isWithValidSearchQuery = searchQueryValue.length >= MINIMAL_SEARCH_QUERY_LENGTH;

    const [{ data: autocompleteSearchData, fetching: areAutocompleteSearchDataFetching }] = useAutocompleteSearchQuery({
        variables: {
            search: debouncedSearchQuery,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
            isAutocomplete: true,
            userIdentifier,
        },
        pause: debouncedSearchQuery.length < MINIMAL_SEARCH_QUERY_LENGTH,
        requestPolicy: 'network-only',
    });

    useEffect(() => {
        setSearchData(autocompleteSearchData);
    }, [autocompleteSearchData]);

    useEffect(() => {
        if (!isWithValidSearchQuery) {
            setSearchData(undefined);
        }
    }, [searchQueryValue]);

    const isSearchResultsPopupVisible =
        isSearchResultsPopupOpen && isWithValidSearchQuery && (!!searchData || areAutocompleteSearchDataFetching);

    const handleSearch = () => {
        if (isWithValidSearchQuery) {
            router.push({
                pathname: searchUrl,
                query: { q: searchQueryValue },
            });
            setIsSearchResultsPopupOpen(false);
        }
    };

    useGtmAutocompleteResultsViewEvent(searchData, debouncedSearchQuery);

    return (
        <>
            <div
                className={twJoin('relative flex w-full transition-all', isWithValidSearchQuery && 'z-aboveOverlay')}
                onFocus={() => setIsSearchResultsPopupOpen(true)}
            >
                <SearchInput
                    className="w-full"
                    label={t('Write what you are looking for...')}
                    shouldShowSpinnerInInput={areAutocompleteSearchDataFetching}
                    value={searchQueryValue}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
                    onSearch={handleSearch}
                />

                <AnimatePresence>
                    {isSearchResultsPopupVisible && (
                        <AutocompleteSearchPopup
                            areAutocompleteSearchDataFetching={areAutocompleteSearchDataFetching}
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
