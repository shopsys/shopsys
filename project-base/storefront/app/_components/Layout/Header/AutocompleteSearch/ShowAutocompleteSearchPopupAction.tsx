'use server';

import { AutocompleteSearchArticlesResult } from './AutocompleteSearchArticlesResult';
import { AutocompleteSearchBrandsResult } from './AutocompleteSearchBrandsResult';
import { AutocompleteSearchCategoriesResult } from './AutocompleteSearchCategoriesResult';
import { AutocompleteSearchProductsResult } from './AutocompleteSearchProductsResult';
import { AUTOCOMPLETE_CATEGORY_LIMIT, AUTOCOMPLETE_PRODUCT_LIMIT } from './constants';
import { getAutocompleteSearchQuery } from 'app/_queries/getAutocompleteSearchQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { getInternationalizedStaticUrls } from 'app/_utils/getInternationalizedStaticUrls';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { IconImage } from 'components/Basic/IconImage/IconImage';
import { LinkButton } from 'components/Forms/Button/LinkButton';

// ❗ WARNING: This file uses an unconventional pattern for server actions.
// This is a hack to return TSX directly from a server action, which is not the typical usage pattern.
// It's based on the approach described in https://www.nico.fyi/blog/react-server-actions-returns-jsx
// where server actions are used to generate and return HTML components directly.
// While this works, be aware that it's not the standard pattern recommended in the React/Next.js documentation.

export const ShowAutocompleteSearchPopupAction = async (searchQuery: string) => {
    if (searchQuery.length === 0) {
        return null;
    }

    const t = await getTranslation();
    const [searchUrl] = getInternationalizedStaticUrls(['/search']);

    const { userIdentifier } = await getCookieStoreStateFromServer();

    const searchData = await getAutocompleteSearchQuery({
        search: searchQuery,
        maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
        maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
        isAutocomplete: true,
        userIdentifier,
    });

    const { articlesSearch, brandSearch, categoriesSearch, productsSearch } = searchData || {};

    const isWithResults = !!(
        articlesSearch?.length ||
        brandSearch?.length ||
        (categoriesSearch && categoriesSearch.totalCount > 0) ||
        (productsSearch && productsSearch.totalCount > 0)
    );

    return (
        <>
            {!isWithResults && (
                <div className="flex items-center">
                    <IconImage alt="warning" icon="warning" />
                    <span className="flex-1 pl-4 text-sm">{t('Could not find any results for the given query.')}</span>
                </div>
            )}

            {isWithResults && (
                <>
                    {productsSearch && (
                        <AutocompleteSearchProductsResult
                            // autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                            productsSearch={productsSearch}
                            // onClosePopupCallback={onClosePopupCallback}
                        />
                    )}

                    {brandSearch && (
                        <AutocompleteSearchBrandsResult
                            // autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                            brandSearch={brandSearch}
                            // onClosePopupCallback={onClosePopupCallback}
                        />
                    )}

                    {categoriesSearch && (
                        <AutocompleteSearchCategoriesResult
                            // autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                            categoriesSearch={categoriesSearch}
                            // onClosePopupCallback={onClosePopupCallback}
                        />
                    )}

                    {articlesSearch && (
                        <AutocompleteSearchArticlesResult
                            articlesSearch={articlesSearch}
                            // autocompleteSearchQueryValue={autocompleteSearchQueryValue}
                            // onClosePopupCallback={onClosePopupCallback}
                        />
                    )}

                    <div className="flex justify-center">
                        <LinkButton
                            className="w-full md:w-fit"
                            href={searchUrl + `?q=${searchQuery}`}
                            size="xlarge"
                            variant="inverted"
                        >
                            {t('View all results')}
                        </LinkButton>
                    </div>
                </>
            )}
        </>
    );
};
