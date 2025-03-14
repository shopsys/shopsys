'use client';

import { SearchResultLink, SearchResultSectionGroup, SearchResultSectionTitle } from './AutocompleteSearchPopup';
import { AUTOCOMPLETE_BRAND_LIMIT } from './constants';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { onGtmAutocompleteResultClickEventHandler } from 'gtm/handlers/onGtmAutocompleteResultClickEventHandler';

type AutocompleteSearchBrandsResultProps = {
    brandSearch: TypeAutocompleteSearchQuery['brandSearch'];
    onClosePopupCallback: () => void;
    autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchBrandsResult: FC<AutocompleteSearchBrandsResultProps> = ({
    autocompleteSearchQueryValue,
    brandSearch,
    onClosePopupCallback,
}) => {
    const { t } = useTranslation();

    if (!brandSearch.length) {
        return null;
    }

    return (
        <div>
            <SearchResultSectionTitle>
                {t('Brands')}
                {` (${brandSearch.length})`}
            </SearchResultSectionTitle>

            <SearchResultSectionGroup>
                {brandSearch.slice(0, AUTOCOMPLETE_BRAND_LIMIT).map((brand) => (
                    <li key={brand.slug}>
                        <SearchResultLink
                            href={brand.slug}
                            type="brand"
                            onClick={() => {
                                onGtmAutocompleteResultClickEventHandler(
                                    autocompleteSearchQueryValue,
                                    GtmSectionType.brand,
                                    brand.name,
                                );
                                onClosePopupCallback();
                            }}
                        >
                            {brand.name}
                        </SearchResultLink>
                    </li>
                ))}
            </SearchResultSectionGroup>
        </div>
    );
};
