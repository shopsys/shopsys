import {
    AutocompleteBodyStyled,
    AutocompleteStyled,
    NoResultsMessageStyled,
    NoResultsMessageWrapperStyled,
    ProductSearchResultImageWrapperStyled,
    ProductSearchResultItemStyled,
    ProductSearchResultLinkStyled,
    ProductSearchResultNameStyled,
    ProductSearchResultPriceStyled,
    ProductsSearchResultStyled,
    SearchResultGroupStyled,
    SearchResultGroupTitleStyled,
    SearchResultLinkStyled,
    ShowAllResultsButtonWrapper,
} from './Autocomplete.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { onClickProductDetailGtmEventHandler, onClickSuggestResultGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType, SimpleProductType } from 'types/product';
import { AutocompleteSearchType } from 'types/search';

export const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
export const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
export const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
export const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchType | undefined;
    isAutocompleteActive: boolean;
    autocompleteSearchQueryValue: string;
};

const TEST_IDENTIFIER = 'layout-header-search-autocomplete';

export const Autocomplete: FC<AutocompleteProps> = ({
    autocompleteSearchQueryValue,
    autocompleteSearchResults,
    isAutocompleteActive,
}) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainConfig.url);

    const onProductDetailRedirectHandler = useCallback(
        (product: SimpleProductType | ListedProductType, listName: GtmListNameType, index: number) => {
            onClickProductDetailGtmEventHandler(product, listName, index, domainConfig.url);
            onClickSuggestResultGtmEventHandler(autocompleteSearchQueryValue, 'product', product.fullName);
        },
        [autocompleteSearchQueryValue, domainConfig.url],
    );

    return (
        <AutocompleteStyled isActive={isAutocompleteActive} data-testid={TEST_IDENTIFIER}>
            <AutocompleteBodyStyled isActive={isAutocompleteActive}>
                {(() => {
                    if (autocompleteSearchResults === undefined) {
                        return null;
                    }

                    if (areAllResultsEmpty(autocompleteSearchResults)) {
                        return (
                            <NoResultsMessageWrapperStyled>
                                <Icon iconType="image" icon="warning" alt="warning" />
                                <NoResultsMessageStyled>
                                    {t('Could not find any results for the given query.')}
                                </NoResultsMessageStyled>
                            </NoResultsMessageWrapperStyled>
                        );
                    }

                    return (
                        <>
                            {autocompleteSearchResults.productsSearch.totalCount > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Products')} (${autocompleteSearchResults.productsSearch.totalCount})`}
                                    </SearchResultGroupTitleStyled>
                                    <ProductsSearchResultStyled data-testid={TEST_IDENTIFIER + '-products'}>
                                        {autocompleteSearchResults.productsSearch.products.map(
                                            (product, index) =>
                                                index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                                    <ProductSearchResultItemStyled
                                                        key={product.slug}
                                                        data-testid={TEST_IDENTIFIER + '-products-' + index}
                                                    >
                                                        <NextLink href={product.slug} passHref>
                                                            <ProductSearchResultLinkStyled
                                                                onClick={() =>
                                                                    onProductDetailRedirectHandler(
                                                                        product,
                                                                        'suggest',
                                                                        index,
                                                                    )
                                                                }
                                                            >
                                                                <ProductSearchResultImageWrapperStyled>
                                                                    <Image
                                                                        image={product.image}
                                                                        type="thumbnailMedium"
                                                                        alt={product.fullName}
                                                                    />
                                                                </ProductSearchResultImageWrapperStyled>
                                                                <ProductSearchResultNameStyled>
                                                                    {product.fullName}
                                                                </ProductSearchResultNameStyled>
                                                                <ProductSearchResultPriceStyled>
                                                                    {formatPrice(product.price.priceWithVat)}
                                                                </ProductSearchResultPriceStyled>
                                                            </ProductSearchResultLinkStyled>
                                                        </NextLink>
                                                    </ProductSearchResultItemStyled>
                                                ),
                                        )}
                                    </ProductsSearchResultStyled>
                                </>
                            )}
                            {autocompleteSearchResults.brandSearch.length > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Brands')} (${autocompleteSearchResults.brandSearch.length})`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={TEST_IDENTIFIER + '-brands'}>
                                        {autocompleteSearchResults.brandSearch.map(
                                            (brand, index) =>
                                                index < AUTOCOMPLETE_BRAND_LIMIT && (
                                                    <li
                                                        key={brand.slug}
                                                        data-testid={TEST_IDENTIFIER + '-brands-' + index}
                                                    >
                                                        <NextLink href={brand.slug} passHref>
                                                            <SearchResultLinkStyled
                                                                onClick={() =>
                                                                    onClickSuggestResultGtmEventHandler(
                                                                        autocompleteSearchQueryValue,
                                                                        'brand',
                                                                        brand.name,
                                                                    )
                                                                }
                                                            >
                                                                {brand.name}
                                                            </SearchResultLinkStyled>
                                                        </NextLink>
                                                    </li>
                                                ),
                                        )}
                                    </SearchResultGroupStyled>
                                </>
                            )}
                            {autocompleteSearchResults.categoriesSearch.totalCount > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Categories')} (${
                                            autocompleteSearchResults.categoriesSearch.totalCount
                                        })`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={TEST_IDENTIFIER + '-categories'}>
                                        {autocompleteSearchResults.categoriesSearch.categories.map(
                                            (category, index) =>
                                                index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                                    <li
                                                        key={category.slug}
                                                        data-testid={TEST_IDENTIFIER + '-categories-' + index}
                                                    >
                                                        <NextLink href={category.slug} passHref>
                                                            <SearchResultLinkStyled
                                                                onClick={() =>
                                                                    onClickSuggestResultGtmEventHandler(
                                                                        autocompleteSearchQueryValue,
                                                                        'category',
                                                                        category.name,
                                                                    )
                                                                }
                                                            >
                                                                {category.name}
                                                            </SearchResultLinkStyled>
                                                        </NextLink>
                                                    </li>
                                                ),
                                        )}
                                    </SearchResultGroupStyled>
                                </>
                            )}
                            {autocompleteSearchResults.articlesSearch.length > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Articles')} (${autocompleteSearchResults.articlesSearch.length})`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={TEST_IDENTIFIER + '-articles'}>
                                        {autocompleteSearchResults.articlesSearch.map(
                                            (article, index) =>
                                                index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                                    <li
                                                        key={article.slug}
                                                        data-testid={TEST_IDENTIFIER + '-articles-' + index}
                                                    >
                                                        <NextLink href={article.slug} passHref>
                                                            <SearchResultLinkStyled
                                                                onClick={() =>
                                                                    onClickSuggestResultGtmEventHandler(
                                                                        autocompleteSearchQueryValue,
                                                                        'article',
                                                                        article.name,
                                                                    )
                                                                }
                                                            >
                                                                {article.name}
                                                            </SearchResultLinkStyled>
                                                        </NextLink>
                                                    </li>
                                                ),
                                        )}
                                    </SearchResultGroupStyled>
                                </>
                            )}
                            <ShowAllResultsButtonWrapper>
                                <Button
                                    type="button"
                                    size="small"
                                    onClick={() =>
                                        router.push({
                                            pathname: searchUrl,
                                            query: { q: autocompleteSearchQueryValue },
                                        })
                                    }
                                    testIdentifier={TEST_IDENTIFIER + '-all-button'}
                                >
                                    {t('View all results')}
                                </Button>
                            </ShowAllResultsButtonWrapper>
                        </>
                    );
                })()}
            </AutocompleteBodyStyled>
        </AutocompleteStyled>
    );
};

const areAllResultsEmpty = (autocompleteSearchResults: AutocompleteSearchType | undefined) => {
    if (autocompleteSearchResults === undefined) {
        return false;
    }

    return (
        autocompleteSearchResults.articlesSearch.length === 0 &&
        autocompleteSearchResults.brandSearch.length === 0 &&
        autocompleteSearchResults.categoriesSearch.totalCount === 0 &&
        autocompleteSearchResults.productsSearch.totalCount === 0
    );
};
