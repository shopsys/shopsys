export type TranslationQuery = {
    [name: string]: any;
    ns?: string;
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
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const commonDictionary = () => import('../public/locales/cs/common.json').then((module) => module.default);
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const accessibilityDictionary = () =>
    import('../public/locales/cs/accessibility.json').then((module) => module.default);

type CommonDictionary = Awaited<ReturnType<typeof commonDictionary>>;
type AccessibilityDictionary = Awaited<ReturnType<typeof accessibilityDictionary>>;

export type Dictionary = {
    common: CommonDictionary;
    accessibility: AccessibilityDictionary;
};

type NamespacedKeys<Namespace extends keyof Dictionary> =
    Paths<Dictionary[Namespace]> extends infer Keys ? (Keys extends string ? `${Namespace}:${Keys}` : never) : never;

export type TranslationKeys =
    | Paths<CommonDictionary>
    | Paths<AccessibilityDictionary>
    | NamespacedKeys<'common'>
    | NamespacedKeys<'accessibility'>;

export type Translate = <T extends string>(
    i18nKey: TranslationKeys,
    query?: TranslationQuery | null,
    options?: {
        returnObjects?: boolean;
        fallback?: string | string[];
        default?: T | string;
        ns?: string;
    },
) => string;
