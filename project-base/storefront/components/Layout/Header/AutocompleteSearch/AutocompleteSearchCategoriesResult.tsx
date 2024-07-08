import { SearchResultLink, SearchResultSectionGroup, SearchResultSectionTitle } from './AutocompleteSearchPopup';
import { AUTOCOMPLETE_CATEGORY_LIMIT } from './constants';
import { TypeSimpleCategoryFragment } from 'graphql/requests/categories/fragments/SimpleCategoryFragment.generated';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { onGtmAutocompleteResultClickEventHandler } from 'gtm/handlers/onGtmAutocompleteResultClickEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

type AutocompleteSearchCategoriesResultProps = {
    categoriesSearch: TypeAutocompleteSearchQuery['categoriesSearch'];
    onClosePopupCallback: () => void;
    autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchCategoriesResult: FC<AutocompleteSearchCategoriesResultProps> = ({
    autocompleteSearchQueryValue,
    categoriesSearch,
    onClosePopupCallback,
}) => {
    const { t } = useTranslation();

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<TypeSimpleCategoryFragment>(categoriesSearch.edges),
        [categoriesSearch.edges],
    );

    if (!mappedCategoriesSearchResults?.length) {
        return null;
    }

    return (
        <div>
            <SearchResultSectionTitle>
                {t('Categories')}
                {` (${mappedCategoriesSearchResults.length})`}
            </SearchResultSectionTitle>

            <SearchResultSectionGroup>
                {mappedCategoriesSearchResults.slice(0, AUTOCOMPLETE_CATEGORY_LIMIT).map((category) => (
                    <li key={category.slug}>
                        <SearchResultLink
                            href={category.slug}
                            type="category"
                            onClick={() => {
                                onGtmAutocompleteResultClickEventHandler(
                                    autocompleteSearchQueryValue,
                                    GtmSectionType.category,
                                    category.name,
                                );
                                onClosePopupCallback();
                            }}
                        >
                            {category.name}
                        </SearchResultLink>
                    </li>
                ))}
            </SearchResultSectionGroup>
        </div>
    );
};
