import { OperationDefinitionNode } from 'graphql';
import { Exchange } from 'urql';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { pipe, tap } from 'wonka';

export const operationNameExchange: Exchange =
    ({ forward }) =>
    (ops$) => {
        return pipe(
            ops$,
            tap((operation) => {
                const operationName =
                    (
                        operation.query.definitions.find((definition) => definition.kind === 'OperationDefinition') as
                            | OperationDefinitionNode
                            | undefined
                    )?.name?.value ?? 'UnknownOperation';

                const urlWithoutTrailingSlash = getStringWithoutTrailingSlash(operation.context.url);
                const urlWithOperationName = urlWithoutTrailingSlash + `/${operationName}`;

                const existingHeaders = (() => {
                    if (!operation.context.fetchOptions) {
                        return {};
                    }

                    if (typeof operation.context.fetchOptions === 'function') {
                        return operation.context.fetchOptions().headers ?? {};
                    }

                    return operation.context.fetchOptions.headers ?? {};
                })();

                operation.context = {
                    ...operation.context,
                    fetchOptions: {
                        ...operation.context.fetch,
                        headers: {
                            ...existingHeaders,
                            'X-operation-name': operationName,
                        },
                    },
                    url: urlWithOperationName,
                };
            }),
            forward,
        );
    };
