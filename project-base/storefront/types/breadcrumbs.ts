import { FriendlyPagesTypesKey } from './friendlyUrl';
import { Translate, TranslationKeys } from './translation';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';
import { Url } from 'utils/staticUrls/getInternationalizedStaticUrl';

export type StaticBreadcrumb = { name: TranslationKeys; type?: FriendlyPagesTypesKey; slug?: Url };
export type StaticBreadcrumbsSettings = { [key: string]: StaticBreadcrumb[] };
export type DynamicBreadcrumbs = (pathname: string, t: Translate) => Promise<TypeBreadcrumbFragment[]>;
export type DynamicBreadcrumbsSettings = { [key: string]: DynamicBreadcrumbs };
