import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { Tag } from 'components/Basic/Tag/Tag';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { m } from 'framer-motion';
import { TypeAutocompleteFavoritesQuery } from 'graphql/requests/autocomplete/queries/AutocompleteFavoritesQuery.generated';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { useRouter } from 'next/router';
import { forwardRef } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { twJoin } from 'tailwind-merge';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { fadeAnimation } from 'utils/animations/animationVariants';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useKeypress } from 'utils/useKeyPress';
import { AutocompleteFavoritesResult } from './AutocompleteFavoritesResult';
import { AutocompleteSearchArticlesResult } from './AutocompleteSearchArticlesResult';
import { AutocompleteSearchBrandsResult } from './AutocompleteSearchBrandsResult';
import { AutocompleteSearchCategoriesResult } from './AutocompleteSearchCategoriesResult';
import { AutocompleteSearchProductsResult } from './AutocompleteSearchProductsResult';
import { AutocompleteSkeleton } from './AutocompleteSkeleton';

type AutocompleteProps = {
    autocompleteSearchResults: TypeAutocompleteSearchQuery | undefined;
    autocompleteSearchQueryValue: string;
    onClosePopupCallback: () => void;
    areAutocompleteSearchDataFetching: boolean;
    favoritesData: TypeAutocompleteFavoritesQuery | undefined;
    showFavorites: boolean;
};

export const AutocompleteSearchPopup: FC<AutocompleteProps> = ({
    autocompleteSearchQueryValue,
    areAutocompleteSearchDataFetching,
    autocompleteSearchResults,
    onClosePopupCallback,
    favoritesData,
    showFavorites,
}) => {
    const router = useRouter();
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const { articlesSearch, brandSearch, categoriesSearch, productsSearch } = autocompleteSearchResults || {};

    const isWithResults = !!(
        articlesSearch?.length ||
        brandSearch?.length ||
        categoriesSearch?.edges?.length ||
        productsSearch?.edges?.length
    );

    const showSearchResults = !showFavorites;

    useKeypress('Escape', () => onClosePopupCallback());

    return (
        <RemoveScroll>
            <m.div
                animate="visible"
                exit="hidden"
                initial="hidden"
                variants={fadeAnimation}
                className={twJoin(
                    'absolute -bottom-3 left-0 z-aboveOverlay flex vl:w-192.5 w-full origin-top translate-y-full flex-col gap-5 vl:gap-6 overflow-auto rounded-xl bg-background-default p-5',
                    'max-h-[calc(85vh-169px)] vl:max-h-[calc(98vh-120px)] md:max-h-[calc(98vh-169px)] lg:max-h-[calc(98vh-180px)]',
                )}
            >
                {showFavorites && (
                    <AutocompleteFavoritesResult
                        favoritesData={favoritesData}
                        onClosePopupCallback={onClosePopupCallback}
                    />
                )}
                {showSearchResults && (areAutocompleteSearchDataFetching || !autocompleteSearchResults) && (
                    <AutocompleteSkeleton />
                )}

                {showSearchResults &&
                    !areAutocompleteSearchDataFetching &&
                    autocompleteSearchResults &&
                    !isWithResults && (
                        <div className="flex items-center">
                            <WarningIcon className="mr-2 size-5 text-icon-error" />

                            <span className="flex-1 text-sm">
                                {t('Could not find any results for the given query.')}
                            </span>
                        </div>
                    )}

                {showSearchResults && !areAutocompleteSearchDataFetching && isWithResults && (
                    <>
                        {productsSearch && (
                            <AutocompleteSearchProductsResult
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                productsSearch={productsSearch}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}
                        {brandSearch && (
                            <AutocompleteSearchBrandsResult
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                brandSearch={brandSearch}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}
                        {categoriesSearch && (
                            <AutocompleteSearchCategoriesResult
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                categoriesSearch={categoriesSearch}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}
                        {articlesSearch && (
                            <AutocompleteSearchArticlesResult
                                articlesSearch={articlesSearch}
                                autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                                onClosePopupCallback={onClosePopupCallback}
                            />
                        )}

                        <div className="flex justify-center">
                            <Button
                                className="w-full md:w-fit"
                                variant="inverted"
                                onClick={() => {
                                    onClosePopupCallback();
                                    router.push({
                                        pathname: searchUrl,
                                        query: { q: autocompleteSearchQueryValue },
                                    });
                                }}
                            >
                                {t('View all results')}
                            </Button>
                        </div>
                    </>
                )}
            </m.div>
        </RemoveScroll>
    );
};

export const SearchResultSectionTitle: FC = ({ children }) => {
    return <p className="mb-2 text-lg">{children}</p>;
};

export const SearchResultSectionGroup: FC = ({ children }) => <ul className="flex flex-wrap gap-2">{children}</ul>;

export const SearchResultLink: FC<{ onClick: () => void; href: string; type: FriendlyPagesTypesKey }> = forwardRef(
    ({ children, onClick, href, type }, _) => (
        <Tag href={href} type={type} onClick={onClick}>
            {children}
        </Tag>
    ),
);

SearchResultLink.displayName = 'SearchResultLink';
