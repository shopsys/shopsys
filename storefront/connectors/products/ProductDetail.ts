import { ProductDetailApiType, ProductDetailImageType, ProductDetailType } from 'components/Pages/ProductDetail/types';
import { ProductItemApiType, SliderProductItemType } from 'components/Blocks/Product/types';
import { ImageApiType } from 'components/Basic/Image/types';
import { mapProductPriceData } from './Products';

// @todo - the `sliderProductQuery` is the same as fragment SliderProduct
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

const mapSliderProductApiData = (apiData: ProductItemApiType[], currencyCode: string): SliderProductItemType[] => {
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
