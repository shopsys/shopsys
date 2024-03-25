import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { LastOrderFragment } from 'graphql/requests/orders/fragments/LastOrderFragment.generated';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { MainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { SimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { ListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { GtmCartItemType, GtmListedProductType, GtmProductInterface, GtmShippingInfoType } from 'gtm/types/objects';
import { getStringWithoutTrailingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { ProductInterfaceType } from 'types/product';

export const mapGtmCartItemType = (
    cartItem: CartItemFragment,
    domainUrl: string,
    listIndex?: number,
    quantity?: number,
): GtmCartItemType => {
    const mappedCartItem: GtmCartItemType = {
        ...mapGtmProductInterface(cartItem.product, domainUrl),
        quantity: quantity ?? cartItem.quantity,
    };

    if (listIndex !== undefined) {
        mappedCartItem.listIndex = listIndex + 1;
    }

    return mappedCartItem;
};

export const mapGtmListedProductType = (
    product: ListedProductFragment | SimpleProductFragment,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});

export const mapGtmProductDetailType = (
    product: ProductDetailFragment | MainVariantDetailFragment,
    domainUrl: string,
): GtmProductInterface => mapGtmProductInterface(product, domainUrl);

const mapGtmProductInterface = (productInterface: ProductInterfaceType, domainUrl: string): GtmProductInterface => {
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
        priceWithoutVat: parseFloat(productInterface.price.priceWithoutVat),
        priceWithVat: parseFloat(productInterface.price.priceWithVat),
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

export const mapGtmShippingInfo = (pickupPlace: ListedStoreFragment | null): GtmShippingInfoType => {
    let transportDetail = '';
    const transportExtra = [];

    if (pickupPlace !== null) {
        transportDetail = `${pickupPlace.name}, ${pickupPlace.street}, ${pickupPlace.city}, ${pickupPlace.country.name}, ${pickupPlace.postcode}`;

        transportExtra.push('');
    }

    return {
        transportDetail,
        transportExtra,
    };
};

export const getGtmPickupPlaceFromStore = (store: ListedStoreFragment): ListedStoreFragment => ({
    __typename: 'Store',
    locationLatitude: null,
    locationLongitude: null,
    slug: '',
    identifier: store.identifier,
    name: store.name,
    city: store.city,
    country: {
        __typename: 'Country',
        name: store.country.name,
        code: store.country.code,
    },
    description: store.description ?? '',
    openingHours: store.openingHours,
    postcode: store.postcode,
    street: store.street,
});

export const getGtmPickupPlaceFromLastOrder = (
    pickupPlaceIdentifier: string,
    lastOrder: LastOrderFragment,
): ListedStoreFragment => ({
    __typename: 'Store',
    locationLatitude: null,
    locationLongitude: null,
    slug: '',
    identifier: pickupPlaceIdentifier,
    name: '',
    city: lastOrder.deliveryCity ?? '',
    country: {
        __typename: 'Country',
        name: lastOrder.deliveryCountry?.name ?? '',
        code: lastOrder.deliveryCountry?.code ?? '',
    },
    description: null,
    openingHours: {
        isOpen: false,
        dayOfWeek: 0,
        openingHoursOfDays: [],
    },
    postcode: lastOrder.deliveryPostcode ?? '',
    street: lastOrder.deliveryStreet ?? '',
});
