import { yupResolver as hookFormYupResolver } from '@hookform/resolvers/yup';
import { FieldValues, Resolver } from 'react-hook-form';
import { ObjectSchema } from 'yup';

export const yupResolver = <TFieldValues extends FieldValues>(
    schema: ObjectSchema<Record<keyof TFieldValues, any>>,
): Resolver<TFieldValues> =>
    // Form types describe raw input values; submit handlers perform any required data transformations explicitly.
    hookFormYupResolver(schema, undefined, { raw: true }) as Resolver<TFieldValues>;
