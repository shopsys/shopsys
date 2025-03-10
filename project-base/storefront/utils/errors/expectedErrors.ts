import { ApplicationErrorsType } from './applicationErrors';
import { CombinedError } from 'urql';

export const isExpectedPriceFilterError = (error: CombinedError | undefined) =>
    error?.graphQLErrors.some((error) => {
        if ('userCode' in error.extensions) {
            const errorExtensions = error.extensions as { userCode: ApplicationErrorsType };
            return (
                errorExtensions.userCode === 'access-denied' &&
                (error.message === 'Filtering by price is not allowed for current user.' ||
                    error.message === 'Ordering by price is not allowed for current user.')
            );
        }
        return false;
    });
