import { Cache } from '@urql/exchange-graphcache';

export const invalidateFields = (cache: Cache, fields: string[]): void => {
    const key = 'Query';
    for (const field of cache.inspectFields('Query')) {
        if (fields.includes(field.fieldName)) {
            cache.invalidate(key, field.fieldKey);
        }
    }
};
