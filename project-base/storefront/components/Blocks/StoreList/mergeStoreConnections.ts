import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';

export const mergeStoreConnections = (
    currentStores: TypeListedStoreConnectionFragment,
    nextStores: TypeListedStoreConnectionFragment,
): TypeListedStoreConnectionFragment => {
    const existingStoreIdentifiers = new Set(
        currentStores.edges
            ?.map((edge) => edge?.node?.identifier)
            .filter((identifier): identifier is string => identifier !== undefined) ?? [],
    );
    const nextEdgesWithoutDuplicates =
        nextStores.edges?.filter((edge) => {
            const identifier = edge?.node?.identifier;

            return identifier === undefined || !existingStoreIdentifiers.has(identifier);
        }) ?? [];

    return {
        ...nextStores,
        edges: [...(currentStores.edges ?? []), ...nextEdgesWithoutDuplicates],
    };
};
