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
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

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
    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [{ data, fetching, error }] = usePromotedProductsQueryApi();

    useEffect(() => {
        if (error === undefined) {
            return;
        }

        const parsedErrors = getUserFriendlyErrors(error, t);
        if (parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(parsedErrors.applicationError);
    }, [fetching]);

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
            name: apiProduct.name !== undefined && apiProduct.name !== null ? apiProduct.name : '',
            image:
                apiProduct?.images !== undefined && apiProduct.images.length > 0
                    ? mapProductImageApiData(apiProduct.images)
                    : null,
            price: mapProductPriceApiData(apiProduct.price, currencyCode),
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability:
                apiProduct.availability !== undefined &&
                apiProduct.availability !== null &&
                apiProduct.availability.name !== undefined &&
                apiProduct.availability.name !== null
                    ? apiProduct.availability.name
                    : '',
            flags: mapFlagsApiData(apiProduct.flags),
            stockQuantity:
                apiProduct.stockQuantity !== undefined && apiProduct.stockQuantity !== null
                    ? apiProduct.stockQuantity
                    : 0,
        };
    });
};

const mapProductImageApiData = (apiData: ImageListFragmentApi['images']): ImageType | null => {
    const productImageData = apiData[0];
    if (
        productImageData === undefined ||
        productImageData === null ||
        productImageData.sizes[0] === undefined ||
        productImageData.sizes[0] === null
    ) {
        return null;
    }

    return mapImageSizeApiData(productImageData.sizes[0]);
};

export const mapProductPriceApiData = (
    price: ProductPriceFragmentApi['price'],
    currencyCode: string,
): ProductPriceType => {
    return {
        priceWithVat: Number.parseFloat(
            price.priceWithVat !== undefined && price.priceWithVat !== null ? price.priceWithVat : 0,
        ),
        priceWithoutVat: Number.parseFloat(
            price.priceWithoutVat !== undefined && price.priceWithoutVat !== null ? price.priceWithoutVat : 0,
        ),
        vatAmount: Number.parseFloat(price.vatAmount !== undefined && price.vatAmount !== null ? price.vatAmount : 0),
        isPriceFrom: price.isPriceFrom,
        currencyCode,
    };
};

const mapFlagsApiData = (flags: FlagLabelFragmentApi[]): FlagType[] => {
    return flags.map((flagApi) => {
        return {
            name: flagApi.name !== undefined && flagApi.name !== null ? flagApi.name : '',
            rgbColor: flagApi.rgbColor !== undefined && flagApi.rgbColor !== null ? flagApi.rgbColor : '',
        };
    });
};
