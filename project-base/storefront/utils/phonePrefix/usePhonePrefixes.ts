import {
    TypePhonePrefixesQuery,
    usePhonePrefixesQuery,
} from 'graphql/requests/phonePrefixes/queries/PhonePrefixesQuery.generated';

export type PhonePrefixesType = NonNullable<TypePhonePrefixesQuery['settings']>['phonePrefixes'];
const EMPTY_PHONE_PREFIXES: PhonePrefixesType = [];

export const usePhonePrefixes = () => {
    const [{ data: phonePrefixesData }] = usePhonePrefixesQuery();
    const phonePrefixes = phonePrefixesData?.settings?.phonePrefixes ?? EMPTY_PHONE_PREFIXES;
    const defaultPhonePrefix = phonePrefixes[0] ?? null;

    return {
        phonePrefixes,
        defaultPhonePrefix,
    };
};
