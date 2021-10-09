import {
    ListedProductItemApiType,
    ListedProductItemType,
    ProductItemApiType,
    ProductPriceApiType,
    ProductPriceType,
    SliderProductItemType,
} from 'components/Blocks/Product/types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';
import { useShopsysSelector } from 'redux/main';

export const sliderProductQuery = `
    __typename
    uuid
    slug
    name
    stockQuantity
    flags {
        name
        rgbColor
    }
    images (sizes: "list") {
        sizes {
            url
            width
            height
        }
    }
    availability {
        name
    }
    price {
        priceWithVat
        priceWithoutVat
        vatAmount
        isPriceFrom
    }
    availableStoresCount
    exposedStoresCount
`;

export const promotedProductsQuery = `
        query promotedProducts {
            promotedProducts {
                ${sliderProductQuery}
            }
        }
    ` as const;

export const mapSliderProductApiData = (
    apiData: ProductItemApiType[],
    currencyCode: string,
): SliderProductItemType[] => {
    return apiData.map((apiProduct) => {
        return {
            ...apiProduct,
            detailSlug: apiProduct.slug,
            image: apiProduct.images.length === 0 ? null : apiProduct.images[0].sizes[0],
            price: mapProductPriceData(apiProduct.price, currencyCode),
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability: apiProduct.availability.name,
        };
    });
};

export const mapProductPriceData = (price: ProductPriceApiType, currencyCode: string): ProductPriceType => {
    return {
        ...price,
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        currencyCode,
    };
};

export function mapListedProductNode(data: ListedProductItemApiType, currencyCode: string): ListedProductItemType {
    return {
        ...data,
        detailSlug: data.slug,
        image: data.images.length === 0 ? null : data.images[0].sizes[0],
        price: mapProductPriceData(data.price, currencyCode),
        isMainVariant: data.__typename === 'MainVariant',
        availability: data.availability.name,
    };
}

export const getPromotedProducts = (): SliderProductItemType[] | undefined => {
    const result = useFetchQuery({ query: promotedProductsQuery });
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const apiData = result?.data?.promotedProducts;
    if (apiData === undefined) {
        return undefined;
    }

    return mapSliderProductApiData(apiData, currencyCode);
};
