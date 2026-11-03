import type { Translate } from 'next-translate';
import { createRequest, type Exchange, gql, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';
import { expect, test, vi } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

const { fetchMock } = vi.hoisted(() => ({ fetchMock: vi.fn<typeof fetch>() }));

vi.mock('@urql/devtools', () => ({ devtoolsExchange: (({ forward }) => forward) satisfies Exchange }));
vi.mock('urql/fetcher', () => ({ fetcher: () => fetchMock }));

const firstQuery = gql`
    query FirstPage {
        notificationBars {
            __typename
            text
        }
    }
`;
const secondQuery = gql`
    query SecondPage {
        settings {
            __typename
            privacyPolicyArticleUrl
        }
    }
`;

test('preserves the browser client and hydrates subsequent pages without refetching', async () => {
    const firstData = { notificationBars: [{ __typename: 'NotificationBar', text: 'First page' }] };
    const secondData = {
        settings: { __typename: 'Settings', privacyPolicyArticleUrl: '/privacy-policy' },
    };
    const cache = ssrExchange({
        isClient: true,
        initialState: {
            [createRequest(firstQuery, {}).key]: { data: JSON.stringify(firstData) },
        },
    });
    const options = {
        t: ((key: string) => key) as Translate,
        ssrExchange: cache,
        domainConfig: { ...defaultTestConfig.domains[0], publicGraphqlEndpoint: 'https://example.com/graphql/' },
    };
    const first = createClient(options);

    expect((await first.query(firstQuery, {}).toPromise()).data).toEqual(firstData);

    cache.restoreData({ [createRequest(secondQuery, {}).key]: { data: JSON.stringify(secondData) } });
    const second = createClient(options);

    expect(second).toBe(first);
    expect((await second.query(secondQuery, {}).toPromise()).data).toEqual(secondData);
    expect(second.readQuery(firstQuery, {})?.data).toEqual(firstData);
    expect(fetchMock).not.toHaveBeenCalled();
});
