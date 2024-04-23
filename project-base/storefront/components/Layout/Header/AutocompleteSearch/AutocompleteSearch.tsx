import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT, MINIMAL_SEARCH_QUERY_LENGTH } from './constants';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
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
    import('./AutocompleteSearchPopup').then((component) => component.AutocompleteSearchPopup),
);

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

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

    const [{ data: fetchedSearchData, fetching: isFetchingSearchData }] = useAutocompleteSearchQuery({
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
        setSearchData(fetchedSearchData);
    }, [fetchedSearchData]);

    useEffect(() => {
        if (!isWithValidSearchQuery) {
            setSearchData(undefined);
        }
    }, [searchQueryValue]);

    const isSearchResultsPopupVisible = isSearchResultsPopupOpen && isWithValidSearchQuery && !!searchData;

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
                className={twJoin('relative flex w-full transition-all', isWithValidSearchQuery && 'z-[10002]')}
                onFocus={() => setIsSearchResultsPopupOpen(true)}
            >
                <SearchInput
                    className="w-full border-2 border-white"
                    isLoading={isFetchingSearchData}
                    label={t("Type what you're looking for")}
                    value={searchQueryValue}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
                    onSearch={handleSearch}
                />

                <div
                    className={twJoin(
                        'absolute left-0 -bottom-3 z-aboveOverlay flex w-full origin-top translate-y-full scale-y-90 flex-col gap-6 rounded bg-whiteSnow p-5 px-7 pb-6 shadow-md transition-all lg:rounded',
                        isSearchResultsPopupVisible
                            ? 'pointer-events-auto scale-y-100 opacity-100'
                            : 'pointer-events-none opacity-0',
                    )}
                >
                    {isSearchResultsPopupVisible && (
                        <AutocompleteSearchPopup
                            autocompleteSearchQueryValue={searchQueryValue}
                            autocompleteSearchResults={searchData}
                            onClickLink={() => setIsSearchResultsPopupOpen(false)}
                        />
                    )}
                </div>
            </div>

            <Overlay isActive={isSearchResultsPopupVisible} onClick={() => setIsSearchResultsPopupOpen(false)} />
        </>
    );
};
