import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmProductInterface } from 'gtm/types/objects';
import { getGtmAvailability } from 'gtm/utils/getGtmAvailability';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { ProductInterfaceType } from 'types/product';
import { getFallbackTimezoneByDomainUrl } from 'utils/domain/domainConfig';
import { getIsoDate } from 'utils/formaters/getIsoDate';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';

type ProductInterfaceWithOptionalZboziCategory = ProductInterfaceType & {
    zboziCategory?: string | null;
};

export const mapGtmProductInterface = (
    productInterface: ProductInterfaceType,
    domainUrl: string,
): GtmProductInterface => {
    let productUrl: string;

    if (domainUrl.endsWith('/')) {
        productUrl = getStringWithoutTrailingSlash(domainUrl) + productInterface.slug;
    } else {
        productUrl = domainUrl + productInterface.slug;
    }

    const zboziCategory = getGtmProductInterfaceZboziCategory(productInterface);
    const availabilityDate = getGtmProductInterfaceAvailabilityDate(productInterface, domainUrl);

    return {
        id: productInterface.id,
        name: productInterface.fullName,
        availability: getGtmAvailability(productInterface.availability.status),
        ...(availabilityDate !== undefined && { availability_date: availabilityDate }),
        imageUrl: mapGtmProductInterfaceImageUrl(productInterface),
        flags: productInterface.flags.map((simpleFlagType) => simpleFlagType.name),
        priceWithoutVat: getGtmPriceBasedOnVisibility(productInterface.price.priceWithoutVat),
        priceWithVat: getGtmPriceBasedOnVisibility(productInterface.price.priceWithVat),
        vatAmount: parseFloat(productInterface.price.vatAmount),
        sku: productInterface.catalogNumber,
        url: productUrl,
        brand: 'brand' in productInterface ? (productInterface.brand?.name ?? '') : '',
        categories:
            'categories' in productInterface ? productInterface.categories.map((category) => category.name) : [],
        ...(zboziCategory !== undefined && { zboziCategory }),
    };
};

const getGtmProductInterfaceAvailabilityDate = (
    productInterface: ProductInterfaceType,
    domainUrl: string,
): string | undefined => {
    if (
        productInterface.availability.status !== TypeAvailabilityStatusEnum.ExpectedRestock ||
        !productInterface.expectedRestockingDate
    ) {
        return undefined;
    }

    return getIsoDate(productInterface.expectedRestockingDate, getFallbackTimezoneByDomainUrl(domainUrl));
};

const getGtmProductInterfaceZboziCategory = (productInterface: ProductInterfaceType): string | undefined => {
    if (!('zboziCategory' in productInterface)) {
        return undefined;
    }

    return (productInterface as ProductInterfaceWithOptionalZboziCategory).zboziCategory ?? undefined;
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
