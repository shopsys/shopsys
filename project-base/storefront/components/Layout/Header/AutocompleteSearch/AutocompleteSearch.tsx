import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT, MINIMAL_SEARCH_QUERY_LENGTH } from './constants';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { AnimatePresence } from 'framer-motion';
import { useAutocompleteFavoritesQuery } from 'graphql/requests/autocomplete/queries/AutocompleteFavoritesQuery.generated';
import { useAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useGtmAutocompleteResultsViewEvent } from 'gtm/utils/pageViewEvents/useGtmAutocompleteResultsViewEvent';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useRef, useState } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { SkeletonEnum } from 'types/skeletons';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useDebounce } from 'utils/useDebounce';
import { useFocusTrap } from 'utils/useFocusTrap';

const AutocompleteSearchPopup = dynamic(() =>
    import('./AutocompleteSearchPopup').then((component) => component.AutocompleteSearchPopup),
);

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

export const AutocompleteSearch: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const searchSectionRef = useRef<HTMLDivElement>(null);
    const searchInputRef = useRef<HTMLInputElement>(null);

    const [isSearchResultsPopupOpen, setIsSearchResultsPopupOpen] = useState(false);
    const [searchQueryValue, setSearchQueryValue] = useState('');

    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

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

    const [{ data: favoritesData }] = useAutocompleteFavoritesQuery();

    const searchData = isWithValidSearchQuery ? autocompleteSearchData : undefined;

    const isWithFavorites = !!(
        favoritesData?.autocompleteFavorites.products.length ||
        favoritesData?.autocompleteFavorites.categories.length ||
        favoritesData?.autocompleteFavorites.brands.length
    );

    const shouldShowPopup = isWithFavorites || isWithValidSearchQuery;
    const isSearchResultsPopupVisible = isSearchResultsPopupOpen && shouldShowPopup;

    const showFavorites = !isWithValidSearchQuery && isWithFavorites;

    const handleSearch = () => {
        if (isWithValidSearchQuery) {
            setIsSearchResultsPopupOpen(false);
            searchInputRef.current?.blur();
            updatePageLoadingState({ redirectPageType: SkeletonEnum.Category });
            router.push({
                pathname: searchUrl,
                query: { q: searchQueryValue },
            });
            setSearchQueryValue('');
        }
    };

    const handleClosePopup = () => {
        setIsSearchResultsPopupOpen(false);
        searchInputRef.current?.focus();
    };

    const handleOpenPopup = () => {
        if (shouldShowPopup) {
            setIsSearchResultsPopupOpen(true);
        }
    };

    useGtmAutocompleteResultsViewEvent(searchData, debouncedSearchQuery);

    useFocusTrap(isSearchResultsPopupVisible ? searchSectionRef : undefined);

    return (
        <>
            <div
                aria-label={t('Site search section', { ns: 'accessibility' })}
                ref={searchSectionRef}
                role="search"
                className={twJoin(
                    'relative flex w-full transition-all',
                    isSearchResultsPopupVisible && 'z-aboveOverlay',
                )}
            >
                <SearchInput
                    aria-haspopup="listbox"
                    ariaLabelForSearchButton={t('Go to search page', { ns: 'accessibility' })}
                    className="w-full"
                    inputRef={searchInputRef}
                    label={t('Write what you are looking for...')}
                    shouldShowSpinnerInInput={areAutocompleteSearchDataFetching}
                    value={searchQueryValue}
                    onChange={(e) => setSearchQueryValue(e.currentTarget.value)}
                    onClear={() => setSearchQueryValue('')}
                    onOpenPopup={handleOpenPopup}
                    onSearch={handleSearch}
                />

                <AnimatePresence>
                    {isSearchResultsPopupVisible && (
                        <AutocompleteSearchPopup
                            areAutocompleteSearchDataFetching={areAutocompleteSearchDataFetching}
                            autocompleteSearchQueryValue={searchQueryValue}
                            autocompleteSearchResults={searchData}
                            favoritesData={favoritesData}
                            showFavorites={showFavorites}
                            onClosePopupCallback={handleClosePopup}
                        />
                    )}
                </AnimatePresence>
            </div>

            <Overlay isActive={isSearchResultsPopupVisible} onClick={handleClosePopup} />
        </>
    );
};
