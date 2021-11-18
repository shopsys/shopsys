import { Cache, cacheExchange, Data } from '@urql/exchange-graphcache';
import { ClientOptions, dedupExchange, fetchExchange } from '@urql/core';
import { CombinedError, errorExchange } from 'urql';
import { authExchange } from '@urql/exchange-auth';
import getAuthExchangeOptions from 'urql/authExchange';
import { GetServerSidePropsContext } from 'next';
import { IntrospectionQuery } from 'graphql';
import { removeTokensFromCookies } from 'utils/Auth/TokensFromCookies';
import schema from 'schema.graphql.json';
import { SSRExchange } from 'next-urql';

const keyUuid = (data: Data) => data?.uuid as string | null;
const keyNull = () => null;

const cache = cacheExchange({
    schema: schema as unknown as IntrospectionQuery,
    keys: {
        Category: keyUuid,
        Image: keyNull,
        ImageSize: keyNull,
        Brand: keyUuid,
        Flag: keyUuid,
        Unit: (data) => data.name as string | null,
        Product: keyUuid,
        Availability: (data) => data.name as string | null,
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
        TransportType: (data) => data.code as string | null,
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
        Country: (data) => data.code as string | null,
    },
    updates: {
        Mutation: {
            Login(_result, _args, cache) {
                invalidateFields(cache, ['MainProduct', 'Variant', 'RegularProduct']);
            },
            Logout(_result, _args, cache) {
                invalidateFields(cache, ['MainProduct', 'Variant', 'RegularProduct']);
            },
        },
    },
});

export const getUrqlExchanges = (
    ssrExchange: SSRExchange,
    context?: GetServerSidePropsContext,
): ClientOptions['exchanges'] => [
    dedupExchange,
    cache,
    ssrExchange,
    errorExchange({
        onError: (error: CombinedError) => {
            const isAuthError = error?.response?.status === 401;

            if (isAuthError) {
                removeTokensFromCookies();
            }
        },
    }),
    authExchange(getAuthExchangeOptions(context)),
    fetchExchange,
];

const invalidateFields = (cache: Cache, fields: string[]): void => {
    const key = 'Query';
    for (const field of cache.inspectFields('Query')) {
        if (fields.includes(field.fieldName)) {
            cache.invalidate(key, field.fieldKey);
        }
    }
};
