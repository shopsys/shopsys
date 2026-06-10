import {
    getTracePropagationTargetFromGraphqlEndpoint,
    getTracePropagationTargetFromInternalEndpoint,
    getTracePropagationTargetsFromPublicGraphqlEndpoints,
} from 'utils/sentry/tracePropagationTargets';
import { describe, expect, it } from 'vitest';

describe('tracePropagationTargets', () => {
    it('returns same-origin GraphQL path target and deduplicated public endpoint targets', () => {
        const targets = getTracePropagationTargetsFromPublicGraphqlEndpoints([
            'https://example.com/graphql/',
            'https://example.com/graphql',
            'not-a-url',
            'https://example.com/sk/graphql/',
        ]);

        expect(targets).toHaveLength(3);
        expect(targets[0]).toBeInstanceOf(RegExp);
        expect((targets[0] as RegExp).test('/graphql/CartQuery')).toBe(true);
        expect((targets[0] as RegExp).test('/sgtm/g/collect')).toBe(false);
        expect(targets.slice(1).map((target) => (target as RegExp).source)).toEqual([
            '^https:\\/\\/example\\.com\\/graphql(?:[/?#]|$)',
            '^https:\\/\\/example\\.com\\/sk\\/graphql(?:[/?#]|$)',
        ]);
    });

    it('matches public GraphQL endpoint variants and rejects unrelated URLs', () => {
        const target = getTracePropagationTargetFromGraphqlEndpoint('https://example.com/graphql/');

        expect(target?.test('https://example.com/graphql/CartQuery')).toBe(true);
        expect(target?.test('https://example.com/graphql?operationName=CartQuery')).toBe(true);
        expect(target?.test('https://example.com/graphql#fragment')).toBe(true);
        expect(target?.test('https://example.com/graphql-other')).toBe(false);
        expect(target?.test('https://example.com/sgtm/g/collect')).toBe(false);
        expect(target?.test('https://piskoviste.shopsys.cz/sgtm/g/collect')).toBe(false);
    });

    it('returns null for invalid public GraphQL endpoint URL', () => {
        expect(getTracePropagationTargetFromGraphqlEndpoint('not-a-url')).toBeNull();
    });

    it('matches default and localized internal GraphQL endpoint URLs', () => {
        const target = getTracePropagationTargetFromInternalEndpoint('http://internal:8000/');

        expect(target?.test('http://internal:8000/graphql/CartQuery')).toBe(true);
        expect(target?.test('http://internal:8000/sk/graphql/CartQuery')).toBe(true);
        expect(target?.test('http://internal:8000/cs/graphql?operationName=CartQuery')).toBe(true);
        expect(target?.test('http://internal:8000/api/log-exception')).toBe(false);
        expect(target?.test('http://internal:8000/sgtm/g/collect')).toBe(false);
    });

    it('returns null for invalid or missing internal endpoint URL', () => {
        expect(getTracePropagationTargetFromInternalEndpoint(undefined)).toBeNull();
        expect(getTracePropagationTargetFromInternalEndpoint('not-a-url')).toBeNull();
    });
});
