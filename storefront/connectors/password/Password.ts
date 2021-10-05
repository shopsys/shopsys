import { useMutation, UseMutationResponse } from 'urql';

const passwordResetMutation = `mutation ($email: String!) {
        RequestPasswordRecovery (email: $email)
    }` as const;

export const usePasswordReset = (): UseMutationResponse<boolean, { email: string }> => {
    return useMutation(passwordResetMutation);
};
