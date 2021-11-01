import {
    FlagLabelFragmentApi,
    ImageListFragmentApi,
    ProductPriceFragmentApi,
    PromotedProductsQueryApi,
    usePromotedProductsQueryApi,
} from 'graphql/generated';
import {
    FlagType,
    ListedProductItemApiType,
    ListedProductItemType,
    ProductPriceApiType,
    ProductPriceType,
    SliderProductItemType,
} from 'components/Blocks/Product/types';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

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
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [{ data, error }] = usePromotedProductsQueryApi();
    useQueryError(error);

    const apiData = data?.promotedProducts;
    if (apiData === undefined) {
        return undefined;
    }

    return mapSliderProductApiData(apiData, currencyCode);
};

export const mapSliderProductApiData = (
    apiData: PromotedProductsQueryApi['promotedProducts'],
    currencyCode: string,
): SliderProductItemType[] => {
    return apiData.map((apiProduct) => {
        return {
            ...apiProduct,
            detailSlug: apiProduct.slug,
            name: apiProduct.name,
            image: mapProductImageApiData(apiProduct.images),
            price: mapProductPriceApiData(apiProduct.price, currencyCode),
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability:
                apiProduct.availability.name !== undefined && apiProduct.availability.name !== null
                    ? apiProduct.availability.name
                    : '',
            flags: mapFlagsApiData(apiProduct.flags),
            stockQuantity: apiProduct.stockQuantity,
        };
    });
};

const mapProductImageApiData = (apiData: ImageListFragmentApi['images']): ImageType | null => {
    if (!(0 in apiData) || !(0 in apiData[0].sizes)) {
        return null;
    }

    return mapImageSizeApiData(apiData[0].sizes[0]);
};

export const mapProductPriceApiData = (
    price: ProductPriceFragmentApi['price'],
    currencyCode: string,
): ProductPriceType => {
    return {
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        isPriceFrom: price.isPriceFrom,
        currencyCode,
    };
};

const mapFlagsApiData = (flags: FlagLabelFragmentApi[]): FlagType[] => {
    return flags.map((flagApi) => {
        return {
            name: flagApi.name !== undefined && flagApi.name !== null ? flagApi.name : '',
            rgbColor: flagApi.rgbColor,
        };
    });
};
