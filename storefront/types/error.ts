import { ApplicationErrorsType } from 'helpers/errors/applicationErrors';

export type ValidationErrors = {
    [fieldName: string]: {
        message: string;
        code: string;
    };
};

export type ParsedErrors = {
    networkError?: string;
    applicationError?: { type: ApplicationErrorsType; message: string };
    userError?: {
        validation?: ValidationErrors;
    };
};
