export const getStateAfterValidation = (
    hasError?: boolean,
    isTouched?: boolean,
    markSuccessfulWhenValid?: boolean,
): 'error' | 'success' | undefined => {
    if (hasError) {
        return 'error';
    }

    if (markSuccessfulWhenValid && isTouched) {
        return 'success';
    }

    return undefined;
};
