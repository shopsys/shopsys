import { GtmProductInterface } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { ProductInterfaceType } from 'types/product';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';

export const mapGtmProductInterface = (
    productInterface: ProductInterfaceType,
    domainUrl: string,
): GtmProductInterface => {
    let productUrl;

    if (domainUrl.endsWith('/')) {
        productUrl = getStringWithoutTrailingSlash(domainUrl) + productInterface.slug;
    } else {
        productUrl = domainUrl + productInterface.slug;
    }

    return {
        id: productInterface.id,
        name: productInterface.fullName,
        availability: productInterface.availability.name,
        imageUrl: mapGtmProductInterfaceImageUrl(productInterface),
        flags: productInterface.flags.map((simpleFlagType) => simpleFlagType.name),
        priceWithoutVat: getGtmPriceBasedOnVisibility(productInterface.price.priceWithoutVat),
        priceWithVat: getGtmPriceBasedOnVisibility(productInterface.price.priceWithVat),
        vatAmount: parseFloat(productInterface.price.vatAmount),
        sku: productInterface.catalogNumber,
        url: productUrl,
        brand: productInterface.brand?.name ?? '',
        categories: productInterface.categories.map((category) => category.name),
    };
};

const mapGtmProductInterfaceImageUrl = (productInterface: ProductInterfaceType): string | undefined => {
    if ('mainImage' in productInterface) {
        return productInterface.mainImage?.url;
    }

    if ('images' in productInterface && Array.isArray(productInterface.images)) {
        return productInterface.images.length ? productInterface.images[0].url : undefined;
    }

    return undefined;
};
