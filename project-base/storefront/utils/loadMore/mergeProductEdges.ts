import { ListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';

export const mergeProductEdges = (
    previousProductEdges?: ListedProductConnectionFragment['edges'],
    newProductEdges?: ListedProductConnectionFragment['edges'],
) => [...(previousProductEdges || []), ...(newProductEdges || [])];
