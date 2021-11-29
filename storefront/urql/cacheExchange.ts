import { Cache, cacheExchange, Data } from '@urql/exchange-graphcache';
import { IntrospectionQuery } from 'graphql';
import schema from 'schema.graphql.json';

const keyNull = () => null;
const keyUuid = (data: Data) => data?.uuid as string | null;
const keyName = (data: Data) => data?.name as string | null;
const keyCode = (data: Data) => data?.code as string | null;

const cache = cacheExchange({
    schema: schema as unknown as IntrospectionQuery,
    keys: {
        Category: keyUuid,
        Image: keyNull,
        ImageSize: keyNull,
        Brand: keyUuid,
        Flag: keyUuid,
        Link: keyNull,
        Unit: keyName,
        Product: keyUuid,
        Availability: keyName,
        ProductPrice: keyNull,
        Parameter: keyUuid,
        ParameterValue: keyUuid,
        File: keyNull,
        StoreAvailability: keyNull,
        RegularProduct: keyUuid,
        MainVariant: keyUuid,
        Variant: keyUuid,
        Payment: keyUuid,
        Price: keyNull,
        Transport: keyUuid,
        TransportType: keyCode,
        Store: keyUuid,
        GoPayPaymentMethod: (data) => data.identifier as string | null,
        CustomerUser: keyUuid,
        DeliveryAddress: keyUuid,
        Order: keyUuid,
        OrderItem: keyNull,
        Article: keyUuid,
        Advert: keyUuid,
        AdvertCode: keyUuid,
        AdvertImage: keyUuid,
        AdvertPosition: (data) => data.positionName as string | null,
        NavigationItem: keyNull,
        NavigationItemCategoriesByColumns: keyNull,
        CompanyCustomerUser: keyUuid,
        RegularCustomerUser: keyUuid,
        BlogArticle: keyUuid,
        BlogCategory: keyUuid,
        SliderItem: keyUuid,
        NotificationBar: keyNull,
        Cart: keyUuid,
        CartItem: keyUuid,
        PersonalDataPage: keyNull,
        PersonalData: keyNull,
        NewsletterSubscriber: keyNull,
        Country: keyCode,
        CartModificationsResult: keyNull,
        CartItemModificationsResult: keyNull,
        CartTransportModificationsResult: keyNull,
        CartPaymentModificationsResult: keyNull,
    },
    updates: {
        Mutation: {
            Login(_result, _args, cache) {
                invalidateFields(cache, ['cart']);
            },
            Logout(_result, _args, cache) {
                invalidateFields(cache, ['cart']);
            },
        },
    },
});

const invalidateFields = (cache: Cache, fields: string[]): void => {
    const key = 'Query';
    for (const field of cache.inspectFields('Query')) {
        if (fields.includes(field.fieldName)) {
            cache.invalidate(key, field.fieldKey);
        }
    }
};

export default cache;
