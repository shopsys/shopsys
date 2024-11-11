export type TranslationQuery = {
    [name: string]: any;
};
type RemovePlural<Key extends string> =
    Key extends `${infer Prefix}${'_zero' | '_one' | '_two' | '_few' | '_many' | '_other' | `_${number}`}`
        ? Prefix
        : Key;
type Join<S1, S2> = S1 extends string ? (S2 extends string ? `${S1}.${S2}` : never) : never;
// @ts-expect-error Type instantiation is excessively deep and possibly infinite
export type Paths<T> = RemovePlural<
    {
        [K in Extract<keyof T, string>]: T[K] extends Record<string, unknown> ? Join<K, Paths<T[K]>> : K;
    }[Extract<keyof T, string>]
>;
const dictionary = () => import('../public/locales/cs/common.json').then((module) => module.default);

export type Dictionary = Awaited<ReturnType<typeof dictionary>>;
export type TranslationKeys = Paths<Dictionary>;

export type Translate = (i18nKey: TranslationKeys, query?: TranslationQuery | null) => string;
