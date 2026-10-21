import { act, render, screen } from '@testing-library/react';
import { getOperationAST, parse } from 'graphql';
import {
    CreateComplaintDocument,
    TypeCreateComplaint,
    TypeCreateComplaintVariables,
} from 'graphql/requests/complaints/mutations/CreateComplaintMutation.generated';
import { useComplaintsQuery } from 'graphql/requests/complaints/queries/ComplaintsQuery.generated';
// biome-ignore lint/style/noRestrictedImports: Exercise production Graphcache with a controlled network transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { expect, test } from 'vitest';

const Complaints = () => {
    const [{ data }] = useComplaintsQuery({ variables: { first: 10 } });

    return (
        <section aria-label="Complaints">
            {data && <p>{data.complaints.totalCount} complaints</p>}
            {data?.complaints.edges?.map((edge) => (
                <p key={edge?.node?.uuid}>{edge?.node?.number}</p>
            ))}
        </section>
    );
};

test('refreshes an already loaded complaint list after an identity-only mutation response', async () => {
    let created = false;
    const operations: string[] = [];
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [dedupExchange, cache, fetchExchange],
        preferGetMethod: false,
        fetch: async (_url, options) => {
            const request = JSON.parse(String(options?.body));
            const operation = getOperationAST(parse(request.query))!.name!.value;
            operations.push(operation);

            if (operation === 'CreateComplaint') {
                created = true;

                return Response.json({
                    data: { CreateComplaint: { __typename: 'Complaint', uuid: 'new-complaint' } },
                });
            }

            if (operation !== 'ComplaintsQuery') {
                throw new Error(`Unexpected operation: ${operation}`);
            }

            return Response.json({
                data: {
                    __typename: 'Query',
                    complaints: {
                        __typename: 'ComplaintConnection',
                        totalCount: created ? 1 : 0,
                        pageInfo: {
                            __typename: 'PageInfo',
                            hasNextPage: false,
                            hasPreviousPage: false,
                            endCursor: created ? 'first-complaint' : null,
                        },
                        edges: created
                            ? [
                                  {
                                      __typename: 'ComplaintEdge',
                                      cursor: 'first-complaint',
                                      node: {
                                          __typename: 'Complaint',
                                          uuid: 'new-complaint',
                                          number: 'COMPLAINT-001',
                                          createdAt: '2026-01-01T00:00:00+00:00',
                                          status: 'New',
                                          resolution: { __typename: 'ComplaintResolution', name: 'Repair' },
                                          items: [],
                                      },
                                  },
                              ]
                            : [],
                    },
                    complaintStatusCounts: [],
                },
            });
        },
    });
    render(
        <Provider value={client}>
            <Complaints />
        </Provider>,
    );
    expect(await screen.findByText('0 complaints')).toBeInTheDocument();

    await act(async () => {
        const result = await client
            .mutation<TypeCreateComplaint, TypeCreateComplaintVariables>(CreateComplaintDocument, {
                input: {
                    email: 'customer@example.com',
                    resolution: 'repair',
                    items: [{ manualComplaintItemName: 'Product', quantity: 1, description: 'Does not work' }],
                    deliveryAddress: {
                        firstName: 'Test',
                        lastName: 'Customer',
                        street: 'Street 1',
                        city: 'Prague',
                        postcode: '11000',
                        country: 'CZ',
                    },
                },
            })
            .toPromise();
        expect(result.error).toBeUndefined();
        expect(result.data?.CreateComplaint.uuid).toBe('new-complaint');
    });

    expect(await screen.findByText('COMPLAINT-001')).toBeInTheDocument();
    expect(screen.getByText('1 complaints')).toBeInTheDocument();
    expect(operations).toEqual(['ComplaintsQuery', 'CreateComplaint', 'ComplaintsQuery']);
});
