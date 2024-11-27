import { getErrorExchange } from './errorExchange';
import { createClient } from 'app/_urql/createClient';
import 'server-only';
import { AnyVariables, Client, DocumentInput, OperationContext, OperationResult, OperationResultSource } from 'urql';

let client: (() => Client) | undefined;

function createMutex() {
    let activeLock = Promise.resolve();

    return () => {
        // Function to release the current lock
        let releaseLock: () => void;
        const newLock = new Promise<void>((resolve) => {
            releaseLock = () => resolve();
        });

        // Wait for the active lock to resolve before allowing this lock to proceed
        const waitForLock = activeLock.then(() => releaseLock);

        // Update the active lock to the newly created lock
        activeLock = newLock;

        // Return a promise that resolves when the caller can proceed
        return waitForLock;
    };
}

const mutexLock = createMutex();

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
    const response = await client.readQuery(query, variables, context);

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
    const unlock = await mutexLock();

    if (!client) {
        const newClient = await createClient();
        // race condition is prevented by mutex
        // eslint-disable-next-line require-atomic-updates
        client = newClient;
    }

    unlock();

    return client();
}
