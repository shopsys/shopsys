import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT } from './Autocomplete';
import { Icon } from 'components/Basic/Icon/Icon';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useGtmAutocompleteResultsViewEvent } from 'gtm/hooks/useGtmAutocompleteResultsViewEvent';
import { useDebounce } from 'hooks/helpers/useDebounce';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useDomainConfig } from 'hooks/useDomainConfig';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { ChangeEventHandler, useCallback, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useAutocompleteSearchQueryApi } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';

const Autocomplete = dynamic(() => import('./Autocomplete').then((component) => component.Autocomplete));

export const MINIMAL_SEARCH_QUERY_LENGTH = 3 as const;
const TEST_IDENTIFIER = 'layout-header-search-autocomplete-input';

export const AutocompleteSearch: FC = () => {
    const router = useRouter();
    const [autocompleteSearchQueryValue, setAutocompleteSearchQueryValue] = useState('');
    const debouncedAutocompleteSearchQuery = useDebounce(autocompleteSearchQueryValue, 200);
    const [hasAutocompleteSearchFocus, setAutocompleteSearchFocus] = useState(false);
    const [{ data: autocompleteSearchData, fetching: areSearchResultsLoading }] = useAutocompleteSearchQueryApi({
        variables: {
            search: debouncedAutocompleteSearchQuery,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
        },
        pause: debouncedAutocompleteSearchQuery.length < MINIMAL_SEARCH_QUERY_LENGTH,
        requestPolicy: 'network-only',
    });

    const autocompleteSearchInRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const t = useTypedTranslationFunction();
    const [isDesktop, setIsDesktop] = useState(false);
    const { width } = useGetWindowSize();

    useGtmAutocompleteResultsViewEvent(autocompleteSearchData, debouncedAutocompleteSearchQuery);

    useEffect(() => {
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
    }, []);

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
            <div className="h-12 lg:relative">
                <div
                    className={twJoin(
                        'transition lg:absolute lg:left-0 lg:top-0 lg:z-[1] lg:max-h-[50px] lg:w-full',
                        hasAutocompleteSearchFocus && 'lg:max-h-auto',
                    )}
                    ref={autocompleteSearchInRef}
                >
                    <div
                        className={twJoin(
                            'relative flex w-full transition-all lg:focus-within:w-[576px]',
                            hasAutocompleteSearchFocus && 'z-[1021] lg:w-[576px]',
                        )}
                    >
                        <SearchInput
                            className={twJoin(
                                'border-2',
                                hasAutocompleteSearchFocus
                                    ? 'max-vl:w-full max-vl:!border-primaryLight'
                                    : 'border-white',
                            )}
                            label={t("Type what you're looking for")}
                            dataTestId={TEST_IDENTIFIER}
                            onEnterPressCallback={onAutocompleteSearchHandler}
                            value={autocompleteSearchQueryValue}
                            onChange={onChangeAutocompleteSearchQueryValueHandler}
                            isLoading={areSearchResultsLoading}
                        />
                        {hasAutocompleteSearchFocus && autocompleteSearchQueryValue.length > 0 && (
                            <div
                                className="absolute -top-8 right-0 flex h-10 w-16 min-w-[72px] -translate-y-1/2 cursor-pointer items-center justify-center rounded bg-orangeLight px-2 transition lg:right-11 lg:top-1/2 lg:h-5 lg:w-5 lg:min-w-fit lg:rounded-full lg:bg-greyLighter lg:px-0"
                                onClick={() => setAutocompleteSearchQueryValue('')}
                            >
                                {isDesktop ? (
                                    <Icon iconType="icon" icon="Close" />
                                ) : (
                                    <>
                                        <div className="flex w-4 items-center justify-center">
                                            <Icon iconType="icon" icon="Close" />
                                        </div>
                                        <span className="ml-1 w-7 text-xs">{t('Close')}</span>
                                    </>
                                )}
                            </div>
                        )}
                    </div>
                    <Autocomplete
                        autocompleteSearchResults={autocompleteSearchData}
                        isAutocompleteActive={
                            hasAutocompleteSearchFocus &&
                            autocompleteSearchQueryValue.length >= MINIMAL_SEARCH_QUERY_LENGTH &&
                            autocompleteSearchData !== undefined
                        }
                        autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                    />
                </div>
            </div>
        </>
    );
};
