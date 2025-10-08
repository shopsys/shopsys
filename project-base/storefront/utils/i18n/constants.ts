export const I18N_NAMESPACES = {
    COMMON: 'common',
    ACCESSIBILITY: 'accessibility',
} as const;

export type I18nNamespace = (typeof I18N_NAMESPACES)[keyof typeof I18N_NAMESPACES];

export const AVAILABLE_NAMESPACES = Object.values(I18N_NAMESPACES);
