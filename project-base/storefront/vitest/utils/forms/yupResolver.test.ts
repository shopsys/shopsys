import { ResolverOptions } from 'react-hook-form';
import { yupResolver } from 'utils/forms/yupResolver';
import { describe, expect, test } from 'vitest';
import { number, object } from 'yup';

type FormValues = {
    age: string;
};

const resolverOptions: ResolverOptions<FormValues> = {
    criteriaMode: 'firstError',
    fields: {},
    names: ['age'],
    shouldUseNativeValidation: false,
};

describe('yupResolver', () => {
    test('returns raw form values after successful validation', async () => {
        const resolver = yupResolver<FormValues>(object({ age: number().required() }));

        const result = await resolver({ age: '42' }, undefined, resolverOptions);

        expect(result).toEqual({ errors: {}, values: { age: '42' } });
    });

    test('returns validation errors for invalid raw values', async () => {
        const resolver = yupResolver<FormValues>(object({ age: number().required() }));

        const result = await resolver({ age: 'not-a-number' }, undefined, resolverOptions);

        expect(result.errors.age).toBeDefined();
        expect(result.values).toEqual({});
    });
});
