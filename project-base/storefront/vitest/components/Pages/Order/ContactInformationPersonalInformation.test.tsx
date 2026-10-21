import { act, cleanup, fireEvent, render, screen } from '@testing-library/react';
import { ContactInformationPersonalInformation } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationPersonalInformation';
import { FormProvider, useForm } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
// biome-ignore lint/style/noRestrictedImports: Use the real query hook with controlled responses, without production networking.
import { createClient, fetchExchange, Provider } from 'urql';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { auth, updateContactInformation } = vi.hoisted(() => ({
    auth: { loggedIn: false },
    updateContactInformation: vi.fn(),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: () => auth.loggedIn }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('store/usePersistStore', () => ({ usePersistStore: () => updateContactInformation }));
vi.mock('store/useSessionStore', () => ({ useSessionStore: () => vi.fn() }));
vi.mock('components/Forms/PhonePrefixSelect/PhoneNumberInputControlled', () => ({
    PhoneNumberInputControlled: () => null,
}));
vi.mock('components/Pages/Order/ContactInformation/contactInformationFormMeta', () => ({
    useContactInformationFormMeta: () => ({
        formName: 'contact-information-form',
        fields: {
            email: { name: 'email', label: 'Your email' },
            firstName: { name: 'firstName', label: 'First name' },
            lastName: { name: 'lastName', label: 'Last name' },
            telephone: { name: 'telephone', label: 'Phone' },
            telephonePrefix: { name: 'telephonePrefix' },
            telephonePrefixCountryCode: { name: 'telephonePrefixCountryCode' },
        },
    }),
}));

type RegistrationRequest = { email: string; respond: (registered: boolean) => void; fail: () => void };

const setup = () => {
    const requests: RegistrationRequest[] = [];
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [fetchExchange],
        preferGetMethod: false,
        fetch: (_input, init) =>
            new Promise<Response>((resolve) => {
                const { variables } = JSON.parse(init?.body as string);
                const respondWith = (body: unknown) =>
                    resolve(new Response(JSON.stringify(body), { headers: { 'Content-Type': 'application/json' } }));
                requests.push({
                    email: variables.email,
                    respond: (registered) => respondWith({ data: { isCustomerUserRegistered: registered } }),
                    fail: () => respondWith({ errors: [{ message: 'Registration lookup unavailable' }] }),
                });
            }),
    });
    const CheckoutForm = () => {
        const form = useForm<ContactInformation>({
            mode: 'onTouched',
            defaultValues: { email: '', firstName: '', lastName: '' },
        });
        return (
            <Provider value={client}>
                <FormProvider {...form}>
                    <ContactInformationPersonalInformation />
                </FormProvider>
            </Provider>
        );
    };
    render(<CheckoutForm />);
    return { requests, emailInput: screen.getByRole('textbox', { name: 'Your email' }) };
};

const advanceTime = async (milliseconds: number) => {
    await act(async () => {
        await vi.advanceTimersByTimeAsync(milliseconds);
    });
};

const respond = async (request: RegistrationRequest, registered: boolean) => {
    await act(async () => {
        request.respond(registered);
        await vi.advanceTimersByTimeAsync(0);
    });
};

describe('Checkout email registration lookup', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        auth.loggedIn = false;
    });

    afterEach(() => {
        cleanup();
        vi.useRealTimers();
    });

    test('waits 300 ms after the last edit and queries only the final email', async () => {
        const { requests, emailInput } = setup();

        fireEvent.change(emailInput, { target: { value: 'customer@example.co' } });
        await advanceTime(299);
        expect(requests).toHaveLength(0);

        fireEvent.change(emailInput, { target: { value: 'customer@example.com' } });
        await advanceTime(299);
        expect(requests).toHaveLength(0);
        expect(emailInput).toHaveValue('customer@example.com');
        expect(updateContactInformation).toHaveBeenLastCalledWith({ email: 'customer@example.com' });

        await advanceTime(1);
        await advanceTime(0);
        expect(requests.map((request) => request.email)).toEqual(['customer@example.com']);
        await respond(requests[0], true);
        expect(screen.getByRole('button', { name: 'Log in' })).toBeInTheDocument();
    });

    test('does not query an empty or malformed email even before the field is blurred', async () => {
        const { requests, emailInput } = setup();

        await advanceTime(300);
        for (const value of ['customer', 'customer@', 'customer@example.', '']) {
            fireEvent.change(emailInput, { target: { value } });
            await advanceTime(300);
            await advanceTime(0);
        }

        expect(requests).toHaveLength(0);
        expect(screen.queryByRole('button', { name: 'Log in' })).not.toBeInTheDocument();
    });

    test('hides the previous registration result while the edited email is waiting for its own response', async () => {
        const { requests, emailInput } = setup();
        fireEvent.change(emailInput, { target: { value: 'registered@example.com' } });
        await advanceTime(300);
        await advanceTime(0);
        await respond(requests[0], true);
        expect(screen.getByRole('button', { name: 'Log in' })).toBeInTheDocument();

        fireEvent.change(emailInput, { target: { value: 'new@example.com' } });
        expect(screen.queryByRole('button', { name: 'Log in' })).not.toBeInTheDocument();
        await advanceTime(300);
        await advanceTime(0);
        expect(requests.map((request) => request.email)).toEqual(['registered@example.com', 'new@example.com']);
        expect(screen.queryByRole('button', { name: 'Log in' })).not.toBeInTheDocument();
        await respond(requests[1], false);
        expect(screen.queryByRole('button', { name: 'Log in' })).not.toBeInTheDocument();
    });

    test('does not show a late registration response for an email that has been cleared', async () => {
        const { requests, emailInput } = setup();
        fireEvent.change(emailInput, { target: { value: 'registered@example.com' } });
        await advanceTime(300);
        await advanceTime(0);
        fireEvent.change(emailInput, { target: { value: '' } });

        await respond(requests[0], true);
        await advanceTime(300);
        expect(requests).toHaveLength(1);
        expect(screen.queryByRole('button', { name: 'Log in' })).not.toBeInTheDocument();
    });

    test('does not check registration for an already logged-in customer', async () => {
        auth.loggedIn = true;
        const { requests, emailInput } = setup();
        fireEvent.change(emailInput, { target: { value: 'customer@example.com' } });
        await advanceTime(300);
        await advanceTime(0);

        expect(requests).toHaveLength(0);
    });

    test('does not reuse the old registration result when the new lookup fails', async () => {
        const { requests, emailInput } = setup();
        fireEvent.change(emailInput, { target: { value: 'registered@example.com' } });
        await advanceTime(300);
        await advanceTime(0);
        await respond(requests[0], true);
        expect(screen.getByRole('button', { name: 'Log in' })).toBeInTheDocument();

        fireEvent.change(emailInput, { target: { value: 'new@example.com' } });
        await advanceTime(300);
        await advanceTime(0);
        await act(async () => {
            requests[1].fail();
            await vi.advanceTimersByTimeAsync(0);
        });

        expect(screen.queryByRole('button', { name: 'Log in' })).not.toBeInTheDocument();
        expect(emailInput).toHaveValue('new@example.com');
    });
});
