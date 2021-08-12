import { useMutation, UseMutationResponse } from 'urql';

const newsletterSubscriptionMutation = `mutation ($email: String!) {
        NewsletterSubscribe(input:{
            email:$email
        })
    }` as const;

export const useNewsletterSubscription = (): UseMutationResponse<boolean, { email: string }> => {
    return useMutation(newsletterSubscriptionMutation);
};
