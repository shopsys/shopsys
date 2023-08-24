import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { AutocompleteSearchQueryApi, useAutocompleteSearchQueryApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useGtmAutocompleteResultsViewEvent } from 'gtm/hooks/useGtmAutocompleteResultsViewEvent';
import { useDebounce } from 'hooks/helpers/useDebounce';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT } from './AutocompleteSearchPopup';

const AutocompleteSearchPopup = dynamic(() =>
    import('./AutocompleteSearchPopup').then((component) => component.AutocompleteSearchPopup),
);

export const MINIMAL_SEARCH_QUERY_LENGTH = 3 as const;

export const AutocompleteSearch: FC = () => {
    const router = useRouter();
    const [isOpen, setIsOpen] = useState(false);
    const [searchData, setSearchData] = useState<AutocompleteSearchQueryApi>();
    const [searchQueryValue, setSearchQueryValue] = useState('');
    const debouncedSearchQuery = useDebounce(searchQueryValue, 200);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const { t } = useTranslation();

    const isWithValidSearchQuery = searchQueryValue.length >= MINIMAL_SEARCH_QUERY_LENGTH;

    const [{ data: fetchedSearchData, fetching: isFetchingSearchData }] = useAutocompleteSearchQueryApi({
        variables: {
            search: debouncedSearchQuery,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
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

    const isSearchResultsPopupVisible = isOpen && isWithValidSearchQuery && !!searchData;

    useGtmAutocompleteResultsViewEvent(searchData, debouncedSearchQuery);

    return (
        <>
            <div
                className={twJoin('relative flex w-full transition-all', isWithValidSearchQuery && 'z-[10002]')}
                onFocus={() => setIsOpen(true)}
            >
                <SearchInput
                    className="w-full border-2 border-white max-vl:border-primaryLight"
                    label={t("Type what you're looking for")}
                    onEnterPressCallback={() =>
                        router.push({
                            pathname: searchUrl,
                            query: { q: searchQueryValue },
                        })
                    }
                    value={searchQueryValue}
                    isLoading={isFetchingSearchData}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
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
                            autocompleteSearchResults={searchData}
                            autocompleteSearchQueryValue={searchQueryValue}
                            onClickLink={() => setIsOpen(false)}
                        />
                    )}
                </div>
            </div>

            <Overlay isActive={isSearchResultsPopupVisible} onClick={() => setIsOpen(false)} />
        </>
    );
};
