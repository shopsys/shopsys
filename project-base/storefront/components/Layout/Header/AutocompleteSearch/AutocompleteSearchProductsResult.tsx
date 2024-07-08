import { SearchResultSectionTitle } from './AutocompleteSearchPopup';
import { AUTOCOMPLETE_PRODUCT_LIMIT } from './constants';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeSimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { onGtmAutocompleteResultClickEventHandler } from 'gtm/handlers/onGtmAutocompleteResultClickEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

type AutocompleteSearchProductsResultProps = {
    productsSearch: TypeAutocompleteSearchQuery['productsSearch'];
    onClosePopupCallback: () => void;
    autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchProductsResult: FC<AutocompleteSearchProductsResultProps> = ({
    productsSearch,
    onClosePopupCallback,
    autocompleteSearchQueryValue,
}) => {
    const { t } = useTranslation();

    const mappedProductSearchResults = useMemo(
        () => mapConnectionEdges<TypeListedProductFragment>(productsSearch.edges),
        [productsSearch.edges],
    );

    const onProductDetailRedirectHandler = (product: TypeSimpleProductFragment | TypeListedProductFragment) => {
        onGtmAutocompleteResultClickEventHandler(
            autocompleteSearchQueryValue,
            GtmSectionType.product,
            product.fullName,
        );
    };

    if (!mappedProductSearchResults?.length) {
        return null;
    }

    return (
        <div tid={TIDs.layout_header_search_autocomplete_popup_products}>
            <SearchResultSectionTitle>
                {t('Products')}
                {` (${productsSearch.totalCount})`}
            </SearchResultSectionTitle>

            <ProductsSlider
                gtmProductListName={GtmProductListNameType.autocomplete_search_results}
                isWithArrows={false}
                products={mappedProductSearchResults.slice(0, AUTOCOMPLETE_PRODUCT_LIMIT)}
                wrapperClassName="auto-cols-[45%] md:auto-cols-[30%] lg:auto-cols-[20%] vl:auto-cols-[20%]"
                productItemProps={{
                    size: 'small',
                    visibleItemsConfig: { price: true },
                    onClick: (product) => {
                        onProductDetailRedirectHandler(product);
                        onClosePopupCallback();
                    },
                }}
            />
        </div>
    );
};
