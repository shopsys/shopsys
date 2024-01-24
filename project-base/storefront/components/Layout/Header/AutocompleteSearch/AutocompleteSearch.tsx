import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT } from './AutocompleteSearchPopup';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { AutocompleteSearchQueryApi, useAutocompleteSearchQueryApi } from 'graphql/generated';
import { useGtmAutocompleteResultsViewEvent } from 'gtm/hooks/useGtmAutocompleteResultsViewEvent';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useDebounce } from 'hooks/helpers/useDebounce';
import { useCurrentUrl } from 'hooks/useCurrentUrl';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { twJoin } from 'tailwind-merge';

const AutocompleteSearchPopup = dynamic(() =>
    import('./AutocompleteSearchPopup').then((component) => component.AutocompleteSearchPopup),
);

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

export const MINIMAL_SEARCH_QUERY_LENGTH = 3 as const;

export const AutocompleteSearch: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const [isSearchResultsPopupOpen, setIsSearchResultsPopupOpen] = useState(false);
    const [searchData, setSearchData] = useState<AutocompleteSearchQueryApi>();
    const [searchQueryValue, setSearchQueryValue] = useState('');

    const userIdentifier = usePersistStore((store) => store.userId)!;
    const requestingPage = useCurrentUrl();

    const debouncedSearchQuery = useDebounce(searchQueryValue, 200);
    const isWithValidSearchQuery = searchQueryValue.length >= MINIMAL_SEARCH_QUERY_LENGTH;

    const [{ data: fetchedSearchData, fetching: isFetchingSearchData }] = useAutocompleteSearchQueryApi({
        variables: {
            search: debouncedSearchQuery,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
            userIdentifier,
            requestingPage: getUrlWithoutGetParameters(requestingPage),
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
                    className="w-full border-2 border-white max-vl:border-primaryLight"
                    isLoading={isFetchingSearchData}
                    label={t("Type what you're looking for")}
                    value={searchQueryValue}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
                    onSearch={handleSearch}
                />

                <div
                    className={twJoin(
                        'absolute left-0 -bottom-3 z-aboveOverlay flex w-full origin-top translate-y-full scale-y-90 flex-col gap-6 rounded bg-creamWhite p-5 px-7 pb-6 shadow-md transition-all lg:rounded',
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
