'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    SliderItemsQueryDocument,
    TypeSliderItemsQuery,
    TypeSliderItemsQueryVariables,
} from 'graphql/requests/sliderItems/queries/SliderItemsQuery.ssr';

export async function getSliderItems() {
    const result = await createQuery<TypeSliderItemsQuery, TypeSliderItemsQueryVariables>(SliderItemsQueryDocument, {});

    return result.data;
}
