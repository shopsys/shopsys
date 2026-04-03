import { ProductsSlider, VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE } from 'components/Blocks/Product/ProductsSlider';
import { TIDs } from 'cypress/tids';
import { TypeAutocompleteFavoritesQuery } from 'graphql/requests/autocomplete/queries/AutocompleteFavoritesQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { SearchResultLink, SearchResultSectionGroup, SearchResultSectionTitle } from './AutocompleteSearchPopup';

type AutocompleteFavoritesResultProps = {
    favoritesData: TypeAutocompleteFavoritesQuery | undefined;
    onClosePopupCallback: () => void;
};

export const AutocompleteFavoritesResult: FC<AutocompleteFavoritesResultProps> = ({
    favoritesData,
    onClosePopupCallback,
}) => {
    const { t } = useTranslation();

    const { products, categories, brands } = favoritesData?.autocompleteFavorites || {};

    const hasAnyFavorites = !!(products?.length || categories?.length || brands?.length);

    if (!hasAnyFavorites) {
        return null;
    }

    return (
        <div data-tid={TIDs.layout_header_search_autocomplete_popup_favorites}>
            {products && products.length > 0 && (
                <div className="mb-4">
                    <SearchResultSectionTitle>{t('Products')}</SearchResultSectionTitle>

                    <ProductsSlider
                        ariaAnchorName="product-slider-autocomplete-favorites"
                        gtmProductListName={GtmProductListNameType.autocomplete_favorites}
                        highlightFirstItemBadgeText={t('Top pick')}
                        isWithArrows={false}
                        products={products}
                        variant="autocomplete"
                        visibleSliderItems={VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE}
                        productItemProps={{
                            size: 'small',
                            textSize: 'xs',
                            textSizePrice: 'base',
                            visibleItemsConfig: { price: true, priceFromWord: true },
                            onClick: () => {
                                onClosePopupCallback();
                            },
                        }}
                    />
                </div>
            )}

            {categories && categories.length > 0 && (
                <div className="mb-4">
                    <SearchResultSectionTitle>{t('Categories')}</SearchResultSectionTitle>

                    <SearchResultSectionGroup>
                        {categories.map((category) => (
                            <li key={category.uuid}>
                                <SearchResultLink
                                    href={category.slug}
                                    type="category"
                                    onClick={() => {
                                        onClosePopupCallback();
                                    }}
                                >
                                    {category.name}
                                </SearchResultLink>
                            </li>
                        ))}
                    </SearchResultSectionGroup>
                </div>
            )}

            {brands && brands.length > 0 && (
                <div>
                    <SearchResultSectionTitle>{t('Brands')}</SearchResultSectionTitle>

                    <SearchResultSectionGroup>
                        {brands.map((brand) => (
                            <li key={brand.slug}>
                                <SearchResultLink
                                    href={brand.slug}
                                    type="brand"
                                    onClick={() => {
                                        onClosePopupCallback();
                                    }}
                                >
                                    {brand.name}
                                </SearchResultLink>
                            </li>
                        ))}
                    </SearchResultSectionGroup>
                </div>
            )}
        </div>
    );
};
