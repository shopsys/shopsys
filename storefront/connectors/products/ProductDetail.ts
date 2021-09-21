import { mapProductPriceData, mapSliderProductApiData, sliderProductQuery } from './Products';
import { ProductDetailApiType, ProductDetailImageType, ProductDetailType } from 'components/Pages/ProductDetail/types';
import { ImageApiType } from 'components/Basic/Image/types';

export const productDetailBody = `
    uuid
    name
    namePrefix
    nameSuffix
    description
    catalogNumber
    stockQuantity
    price {
        priceWithVat
        priceWithoutVat
        vatAmount
        isPriceFrom
    }
    images (sizes: ["default","galleryThumbnail"]) {
        position
        type
        sizes {
            size
            url
            width
            height
        }
    }
    availability {
        name 
        status
    }
    storeAvailabilities {
        storeName
        exposed
        availabilityInformation
        availabilityStatus
    }
    breadcrumb {
        name
        slug
    }
    availableStoresCount
    exposedStoresCount
    accessories {
        ${sliderProductQuery}
    }
    parameters {
        uuid
        name
        visible
        values {
            uuid
            text
        }
    }
`;

export const mapProductDetailApiData = (
    productDetailApiData: ProductDetailApiType,
    currencyCode: string,
): ProductDetailType => {
    return {
        ...productDetailApiData,
        price: mapProductPriceData(productDetailApiData.price, currencyCode),
        accessories: mapSliderProductApiData(productDetailApiData.accessories, currencyCode),
        parameters: productDetailApiData.parameters.filter((parameter) => parameter.visible),
        images: mapProductDetailImages(productDetailApiData.images),
    };
};

const mapProductDetailImages = (images: ImageApiType[]) => {
    const mappedImages = [];
    for (const image of images) {
        const mappedImage: ProductDetailImageType = {};
        for (const imageSize of image.sizes) {
            mappedImage[imageSize.size] = {
                ...imageSize,
            };
        }
        mappedImages.push(mappedImage);
    }
    return mappedImages;
};
