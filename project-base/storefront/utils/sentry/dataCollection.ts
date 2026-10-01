import type { init } from '@sentry/nextjs';

// Preserve the restrictive v10 defaults instead of opting into v11's broader data collection.
export const sentryDataCollection = {
    userInfo: false,
    cookies: false,
    httpHeaders: {
        request: { deny: ['forwarded', '-ip', 'remote-', 'via', '-user'] },
        response: { deny: ['forwarded', '-ip', 'remote-', 'via', '-user'] },
    },
    httpBodies: [],
    urlQueryParams: { deny: ['forwarded', '-ip', 'remote-', 'via', '-user'] },
    genAI: { inputs: false, outputs: false },
    databaseQueryData: false,
    queues: false,
    graphQL: { document: false, variables: false },
} satisfies NonNullable<Parameters<typeof init>[0]>['dataCollection'];
