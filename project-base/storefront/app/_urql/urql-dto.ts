import { getErrorExchange } from './errorExchange';
import { createClient } from 'app/_urql/createClient';
import { headers } from 'next/headers';
import 'server-only';
import { AnyVariables, Client, DocumentInput, OperationContext, OperationResult, OperationResultSource } from 'urql';

const clients = new Map<string, () => Client>();

export async function createQuery<Data = any, Variables extends AnyVariables = AnyVariables>(
    query: DocumentInput<Data, Variables>,
    variables: Variables,
    context?: Partial<OperationContext>,
): Promise<OperationResultSource<OperationResult<Data, Variables>>> {
    const client = await getClient();
    const response = await client.query(query, variables, context);

    const { error, operation } = response;

    getErrorExchange(error, operation);

    return response;
}

export async function readQuery<Data = any, Variables extends AnyVariables = AnyVariables>(
    query: DocumentInput<Data, Variables>,
    variables: Variables,
    context?: Partial<OperationContext>,
): Promise<OperationResultSource<OperationResult<Data, Variables>>> {
    const client = await getClient();
    const response = client.readQuery(query, variables, context);

    if (!response) {
        return { data: null } as OperationResult<Data, Variables>;
    }

    return response;
}

export async function createMutation<Data = any, Variables extends AnyVariables = AnyVariables>(
    query: DocumentInput<Data, Variables>,
    variables: Variables,
    context?: Partial<OperationContext>,
): Promise<OperationResultSource<OperationResult<Data, Variables>>> {
    const client = await getClient();
    const response = await client.mutation(query, variables, context);

    const { error, operation } = response;

    getErrorExchange(error, operation);

    return response;
}

export async function getClient() {
    const host = (await headers()).get('host')!;

    if (host && !clients.has(host)) {
        const newClient = await createClient();
        clients.set(host, newClient);
    }

    return clients.get(host)!();
}
