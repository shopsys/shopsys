import { ApplicationErrors } from './applicationErrors';

// errors we don't want to display to users
export const ApplicationIgnoredErrors = [
    ApplicationErrors['no-result-found-for-slug'],
    ApplicationErrors['seo-page-not-found'],
    ApplicationErrors['invalid-token'],
] as const;
