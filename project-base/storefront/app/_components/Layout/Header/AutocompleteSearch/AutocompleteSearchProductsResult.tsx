import { AutocompleteSearchResultSection } from './AutocompleteSearchResultSection';
import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapConnectionEdges } from 'utils/mappers/connection';

type AutocompleteSearchProductsResultProps = {
    productsSearch: TypeAutocompleteSearchQuery['productsSearch'];
    // autocompleteSearchQueryValue: string;
};

export const AutocompleteSearchProductsResult: FC<AutocompleteSearchProductsResultProps> = async ({
    productsSearch,
    // autocompleteSearchQueryValue,
}) => {
    const t = await getTranslation();

    const mappedProductSearchResults = mapConnectionEdges<TypeListedProductFragment>(productsSearch.edges);

    // TODO: gtm
    // const onProductDetailRedirectHandler = (product: TypeSimpleProductFragment | TypeListedProductFragment) => {
    //     onGtmAutocompleteResultClickEventHandler(
    //         autocompleteSearchQueryValue,
    //         GtmSectionType.product,
    //         product.fullName,
    //     );
    // };

    if (!mappedProductSearchResults?.length) {
        return null;
    }

    const title = `${t('Products')} ${productsSearch.totalCount !== -1 && `(${productsSearch.totalCount})`}`;

    return (
        <AutocompleteSearchResultSection
            isSlider
            tid={TIDs.layout_header_search_autocomplete_popup_products}
            title={title}
        >
            <ProductSlider
                ariaAnchorName="product-slider-autocomplete"
                isWithArrows={false}
                totalItems={mappedProductSearchResults.length}
                variant="autocomplete"
            >
                {mappedProductSearchResults.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        isShownInSlider
                        gtmMessageOrigin={GtmMessageOriginType.autocomplete_search_results}
                        gtmProductListName={GtmProductListNameType.autocomplete_search_results}
                        listIndex={index}
                        product={product}
                        size="small"
                        textSize="xs"
                        textSizePrice="base"
                        visibleItemsConfig={{ price: true }}
                    />
                ))}
            </ProductSlider>
        </AutocompleteSearchResultSection>
    );
};
