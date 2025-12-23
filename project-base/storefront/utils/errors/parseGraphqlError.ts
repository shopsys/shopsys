import { GraphQLError } from 'graphql';

export type RawValidationEntry = {
    message: string;
    code: string;
};

export type RawValidationErrors = Record<string, RawValidationEntry[]>;

export type GraphqlErrorExtensions = {
    validation?: RawValidationErrors;
    userCode?: string | null;
    code?: string | number;
    file?: string;
    line?: number;
    trace?: Array<{ file: string; line: number; call: string }>;
};

export type ParsedGraphqlError = {
    message: string;
    errorCode: string | number | null;
    userCode: string | null;
    validationErrors: RawValidationErrors | null;
    extensions: GraphqlErrorExtensions | null;
};

export const parseGraphqlErrorExtensions = (
    extensions: GraphqlErrorExtensions | null | undefined,
): {
    errorCode: string | number | null;
    userCode: string | null;
    validationErrors: RawValidationErrors | null;
} => {
    if (!extensions) {
        return {
            errorCode: null,
            userCode: null,
            validationErrors: null,
        };
    }

    return {
        errorCode: extensions.code ?? null,
        userCode: extensions.userCode ?? null,
        validationErrors: extensions.validation ?? null,
    };
};

export const parseGraphqlError = (error: GraphQLError): ParsedGraphqlError => {
    const extensions = error.extensions as GraphqlErrorExtensions | undefined;
    const { errorCode, userCode, validationErrors } = parseGraphqlErrorExtensions(extensions);

    return {
        message: error.message,
        errorCode,
        userCode,
        validationErrors,
        extensions: extensions ?? null,
    };
};
export const parseGraphqlErrorFromJson = (jsonString: string): ParsedGraphqlError | null => {
    try {
        const parsed = JSON.parse(jsonString);

        if (typeof parsed !== 'object' || parsed === null || !('message' in parsed)) {
            return null;
        }

        const extensions = parsed.extensions as GraphqlErrorExtensions | undefined;
        const { errorCode, userCode, validationErrors } = parseGraphqlErrorExtensions(extensions);

        return {
            message: parsed.message,
            errorCode,
            userCode,
            validationErrors,
            extensions: extensions ?? null,
        };
    } catch {
        return null;
    }
};

export const hasValidationErrors = (error: ParsedGraphqlError): boolean => {
    return error.validationErrors !== null && Object.keys(error.validationErrors).length > 0;
};

export const flattenValidationErrors = (
    validationErrors: RawValidationErrors,
    options: { stripInputPrefix?: boolean } = {},
): Array<{ field: string; message: string; code: string }> => {
    const result: Array<{ field: string; message: string; code: string }> = [];
    const { stripInputPrefix = false } = options;

    for (const [fieldName, fieldErrors] of Object.entries(validationErrors)) {
        const cleanFieldName = stripInputPrefix ? fieldName.replace('input.', '') : fieldName;

        for (const error of fieldErrors) {
            result.push({
                field: cleanFieldName,
                message: error.message,
                code: error.code,
            });
        }
    }

    return result;
};

export const getFirstValidationErrorPerField = (
    validationErrors: RawValidationErrors,
    options: { stripInputPrefix?: boolean } = {},
): Record<string, { message: string; code: string }> => {
    const result: Record<string, { message: string; code: string }> = {};
    const { stripInputPrefix = false } = options;

    for (const [fieldName, fieldErrors] of Object.entries(validationErrors)) {
        if (fieldErrors.length > 0) {
            const cleanFieldName = stripInputPrefix ? fieldName.replace('input.', '') : fieldName;
            result[cleanFieldName] = fieldErrors[0];
        }
    }

    return result;
};

export const getEffectiveErrorCode = (error: ParsedGraphqlError): string | number | null => {
    return error.errorCode ?? error.userCode ?? null;
};

export const buildValidationDisplayMessage = (validationErrors: RawValidationErrors): string => {
    const flattened = flattenValidationErrors(validationErrors);
    return flattened.map((e) => `${e.field}: ${e.message}`).join('\n');
};
