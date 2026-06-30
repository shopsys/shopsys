import { ProductsSlider, VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE } from 'components/Blocks/Product/ProductsSlider';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeSimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';
import { onGtmAutocompleteResultClickEventHandler } from 'gtm/handlers/onGtmAutocompleteResultClickEventHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { SearchResultSectionTitle } from './AutocompleteSearchPopup';
import { AUTOCOMPLETE_PRODUCT_LIMIT } from './constants';

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

    const mappedProductSearchResults = mapConnectionEdges<TypeListedProductFragment>(productsSearch.edges);

    const onProductDetailRedirectHandler = (product: TypeListedProductFragment | TypeSimpleProductFragment) => {
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
        <div data-tid={TIDs.layout_header_search_autocomplete_popup_products}>
            <SearchResultSectionTitle>
                {t('Products')}
                {productsSearch.totalCount !== -1 && ` (${productsSearch.totalCount})`}
            </SearchResultSectionTitle>

            <ProductsSlider
                ariaAnchorName="product-slider-autocomplete"
                gtmProductListName={GtmProductListNameType.autocomplete_search_results}
                isWithArrows={false}
                products={mappedProductSearchResults.slice(0, AUTOCOMPLETE_PRODUCT_LIMIT)}
                variant="autocomplete"
                visibleSliderItems={VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE}
                productItemProps={{
                    size: 'small',
                    textSize: 'xs',
                    textSizePrice: 'base',
                    visibleItemsConfig: { price: true, priceFromWord: true },
                    onClick: (product) => {
                        onProductDetailRedirectHandler(product);
                        onClosePopupCallback();
                    },
                }}
            />
        </div>
    );
};
