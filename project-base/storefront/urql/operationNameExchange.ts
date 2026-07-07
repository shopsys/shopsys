import { DocumentNode, OperationDefinitionNode } from 'graphql';
import { Exchange } from 'urql';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { pipe, tap } from 'wonka';

export const operationNameExchange: Exchange =
    ({ forward }) =>
    (ops$) => {
        return pipe(
            ops$,
            tap((operation) => {
                const query = operation.query as DocumentNode;
                const operationName =
                    (
                        query.definitions.find((definition) => definition.kind === 'OperationDefinition') as
                            | OperationDefinitionNode
                            | undefined
                    )?.name?.value ?? 'UnknownOperation';

                const urlWithoutTrailingSlash = getStringWithoutTrailingSlash(operation.context.url);
                const urlWithOperationName = `${urlWithoutTrailingSlash}/${operationName}`;

                const existingFetchOptions = (() => {
                    if (!operation.context.fetchOptions) {
                        return {};
                    }

                    if (typeof operation.context.fetchOptions === 'function') {
                        return operation.context.fetchOptions();
                    }

                    return operation.context.fetchOptions;
                })();

                operation.context = {
                    ...operation.context,
                    fetchOptions: {
                        ...existingFetchOptions,
                        headers: {
                            ...(existingFetchOptions.headers ?? {}),
                            'X-operation-name': operationName,
                        },
                    },
                    url: urlWithOperationName,
                };
            }),
            forward,
        );
    };
