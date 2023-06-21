export const hasValidationErrors = (validationMessagesObject: Record<string, unknown>): boolean =>
    Object.keys(validationMessagesObject).length > 0;
