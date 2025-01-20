'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    RecommendedProductsQueryDocument,
    TypeRecommendedProductsQuery,
    TypeRecommendedProductsQueryVariables,
} from 'graphql/requests/products/queries/RecommendedProductsQuery.ssr';
import { TypeRecommendationType } from 'graphql/types';

type GetRecommendedProductsQueryProp = {
    userIdentifier: string;
    recommendationType: TypeRecommendationType;
    recommenderClientIdentifier: string;
    limit: number;
    itemUuids: string[];
};

export const getRecommendedProductsQuery = async ({
    userIdentifier,
    recommendationType,
    recommenderClientIdentifier,
    limit,
    itemUuids,
}: GetRecommendedProductsQueryProp) => {
    const result = await createQuery<TypeRecommendedProductsQuery, TypeRecommendedProductsQueryVariables>(
        RecommendedProductsQueryDocument,
        {
            userIdentifier,
            recommendationType,
            recommenderClientIdentifier,
            limit,
            itemUuids,
        },
    );

    return result;
};
