import { Translate } from 'next-translate';
import { CombinedError } from 'urql';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';

export const getPaymentSessionExpiredErrorMessage = (
    t: Translate,
    ...combinedErrors: (CombinedError | undefined)[]
) => {
    for (const error of combinedErrors) {
        if (!error?.graphQLErrors.length) {
            continue;
        }

        const { applicationError } = getUserFriendlyErrors(error, t);
        if (applicationError?.type === 'order-sent-page-not-available') {
            return t('Order sent page is not available.');
        }
    }

    return '';
};
