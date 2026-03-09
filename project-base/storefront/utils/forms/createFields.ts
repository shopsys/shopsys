import { ReactElement } from 'react';
import { FormFieldMeta } from 'types/formMeta';

type FieldInput<TExtra> = string | ReactElement | ({ label: string | ReactElement } & TExtra);

const isFieldWithExtras = (value: unknown): value is { label: unknown } =>
    typeof value === 'object' && value !== null && 'label' in value;

/**
 * Shorthand for defining FormFieldMeta — auto-generates `name` from object keys.
 *
 * Each field value can be:
 * - `string | ReactElement` — used as label directly
 * - `{ label, ...extras }` — label + extra properties (e.g. `disabled`)
 *
 * @example
 * // Simple fields (label only):
 * createFields<MyFormType>({
 *     email: t('Your email'),
 *     gdprAgreement: <Trans i18nKey="GdprAgreementCheckbox" ... />,
 * })
 *
 * // Fields with extra properties:
 * createFields<MyFormType, { disabled?: boolean }>({
 *     email: { label: t('Your email'), disabled: isUserLoggedIn },
 *     telephone: t('Phone'),
 * })
 */
// eslint-disable-next-line @typescript-eslint/no-empty-object-type
export const createFields = <T, TExtra = {}>(fields: { [K in keyof T]: FieldInput<TExtra> }): FormFieldMeta<
    T,
    TExtra
> =>
    Object.fromEntries(
        Object.entries(fields).map(([key, value]) => [
            key,
            isFieldWithExtras(value) ? { name: key, ...value } : { name: key, label: value },
        ]),
    ) as FormFieldMeta<T, TExtra>;
