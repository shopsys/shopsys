import { ApplicationErrors, ApplicationErrorsType } from './applicationErrors';

// errors we don't want to display to users
export const ApplicationIgnoredErrors: readonly ApplicationErrorsType[] = [
    ApplicationErrors['no-result-found-for-slug'],
    ApplicationErrors['invalid-token'],
] as const;
