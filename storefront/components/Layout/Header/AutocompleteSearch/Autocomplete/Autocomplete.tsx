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
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType, SimpleProductType } from 'types/product';
import { AutocompleteSearchType } from 'types/search';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { onClickProductDetailGtmEventHandler, onClickSuggestResultGtmEventHandler } from 'utils/Gtm/EventHandlers';

export const AUTOCOMPLETE_PRODUCT_LIMIT = 5 as const;
export const AUTOCOMPLETE_BRAND_LIMIT = 3 as const;
export const AUTOCOMPLETE_CATEGORY_LIMIT = 3 as const;
export const AUTOCOMPLETE_ARTICLE_LIMIT = 3 as const;

type AutocompleteProps = {
    autocompleteSearchResults: AutocompleteSearchType | undefined;
    isAutocompleteActive: boolean;
    autocompleteSearchQueryValue: string;
};

export const Autocomplete: FC<AutocompleteProps> = (props) => {
    const testIdentifier = 'layout-header-search-autocomplete';

    const router = useRouter();
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainConfig.url);

    const onProductDetailRedirectHandler = useCallback(
        (product: SimpleProductType | ListedProductType, listName: GtmListNameType, index: number) => {
            onClickProductDetailGtmEventHandler(product, listName, index, domainConfig.url);
            onClickSuggestResultGtmEventHandler(props.autocompleteSearchQueryValue, 'product', product.fullName);
        },
        [props.autocompleteSearchQueryValue, domainConfig.url],
    );

    return (
        <AutocompleteStyled isActive={props.isAutocompleteActive} data-testid={testIdentifier}>
            <AutocompleteBodyStyled isActive={props.isAutocompleteActive}>
                {(() => {
                    if (props.autocompleteSearchResults === undefined) {
                        return null;
                    }

                    if (areAllResultsEmpty(props.autocompleteSearchResults)) {
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
                            {props.autocompleteSearchResults.productsSearch.totalCount > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Products')} (${
                                            props.autocompleteSearchResults.productsSearch.totalCount
                                        })`}
                                    </SearchResultGroupTitleStyled>
                                    <ProductsSearchResultStyled data-testid={testIdentifier + '-products'}>
                                        {props.autocompleteSearchResults.productsSearch.products.map(
                                            (product, index) =>
                                                index < AUTOCOMPLETE_PRODUCT_LIMIT && (
                                                    <ProductSearchResultItemStyled
                                                        key={product.slug}
                                                        data-testid={testIdentifier + '-products-' + index}
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
                            {props.autocompleteSearchResults.brandSearch.length > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Brands')} (${props.autocompleteSearchResults.brandSearch.length})`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={testIdentifier + '-brands'}>
                                        {props.autocompleteSearchResults.brandSearch.map(
                                            (brand, index) =>
                                                index < AUTOCOMPLETE_BRAND_LIMIT && (
                                                    <li
                                                        key={brand.slug}
                                                        data-testid={testIdentifier + '-brands-' + index}
                                                    >
                                                        <NextLink href={brand.slug} passHref>
                                                            <SearchResultLinkStyled
                                                                onClick={() =>
                                                                    onClickSuggestResultGtmEventHandler(
                                                                        props.autocompleteSearchQueryValue,
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
                            {props.autocompleteSearchResults.categoriesSearch.totalCount > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Categories')} (${
                                            props.autocompleteSearchResults.categoriesSearch.totalCount
                                        })`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={testIdentifier + '-categories'}>
                                        {props.autocompleteSearchResults.categoriesSearch.categories.map(
                                            (category, index) =>
                                                index < AUTOCOMPLETE_CATEGORY_LIMIT && (
                                                    <li
                                                        key={category.slug}
                                                        data-testid={testIdentifier + '-categories-' + index}
                                                    >
                                                        <NextLink href={category.slug} passHref>
                                                            <SearchResultLinkStyled
                                                                onClick={() =>
                                                                    onClickSuggestResultGtmEventHandler(
                                                                        props.autocompleteSearchQueryValue,
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
                            {props.autocompleteSearchResults.articlesSearch.length > 0 && (
                                <>
                                    <SearchResultGroupTitleStyled>
                                        {`${t('Articles')} (${props.autocompleteSearchResults.articlesSearch.length})`}
                                    </SearchResultGroupTitleStyled>
                                    <SearchResultGroupStyled data-testid={testIdentifier + '-articles'}>
                                        {props.autocompleteSearchResults.articlesSearch.map(
                                            (article, index) =>
                                                index < AUTOCOMPLETE_ARTICLE_LIMIT && (
                                                    <li
                                                        key={article.slug}
                                                        data-testid={testIdentifier + '-articles-' + index}
                                                    >
                                                        <NextLink href={article.slug} passHref>
                                                            <SearchResultLinkStyled
                                                                onClick={() =>
                                                                    onClickSuggestResultGtmEventHandler(
                                                                        props.autocompleteSearchQueryValue,
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
                                            query: { q: props.autocompleteSearchQueryValue },
                                        })
                                    }
                                    data-testid={testIdentifier + '-all-button'}
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
