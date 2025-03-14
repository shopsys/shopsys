import { AutocompleteSearchResultSection } from './AutocompleteSearchResultSection';
import { AUTOCOMPLETE_BRAND_LIMIT } from './constants';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';

type AutocompleteSearchBrandsResultProps = {
    brandSearch: TypeAutocompleteSearchQuery['brandSearch'];
    // autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchBrandsResult: FC<AutocompleteSearchBrandsResultProps> = async ({
    brandSearch,
    // autocompleteSearchQueryValue,
}) => {
    const t = await getTranslation();

    if (!brandSearch.length) {
        return null;
    }

    const title = `${t('Brands')} (${brandSearch.length})`;

    return (
        <AutocompleteSearchResultSection title={title}>
            {brandSearch.slice(0, AUTOCOMPLETE_BRAND_LIMIT).map((brand) => (
                <li key={brand.slug}>
                    <LabelLink
                        href={brand.slug}
                        type="brand"
                        // onClick={() => {
                        // onGtmAutocompleteResultClickEventHandler(
                        //     autocompleteSearchQueryValue,
                        //     GtmSectionType.brand,
                        //     brand.name,
                        // );
                        // }}
                    >
                        {brand.name}
                    </LabelLink>
                </li>
            ))}
        </AutocompleteSearchResultSection>
    );
};
