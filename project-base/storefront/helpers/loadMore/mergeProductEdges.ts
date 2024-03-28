import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';

export const mergeProductEdges = (
    previousProductEdges?: TypeListedProductConnectionFragment['edges'],
    newProductEdges?: TypeListedProductConnectionFragment['edges'],
) => [...(previousProductEdges || []), ...(newProductEdges || [])];
