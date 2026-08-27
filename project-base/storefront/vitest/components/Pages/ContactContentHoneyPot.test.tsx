import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { ContactContent } from 'components/Pages/Contact/ContactContent';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { contactFormMock } = vi.hoisted(() => ({
    contactFormMock: vi.fn(),
}));

vi.mock('graphql/requests/contact/mutations/ContactFormMutation.generated', () => ({
    useContactFormMutation: () => [{}, contactFormMock],
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: undefined }],
}));

vi.mock('connectors/customer/CurrentCustomer', () => ({
    useCurrentCustomerData: () => undefined,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('ContactContent honey pot', () => {
    beforeEach(() => {
        contactFormMock.mockClear();
        contactFormMock.mockResolvedValue({ data: { ContactForm: true }, error: undefined });
    });

    test('sends the value a bot filled into the hidden field', async () => {
        const { container } = render(<ContactContent />);

        const honeyPotInput = container.querySelector('input[name="subject"]');
        expect(honeyPotInput).not.toBeNull();

        fireEvent.change(screen.getByRole('textbox', { name: 'Your name' }), { target: { value: 'Bot' } });
        fireEvent.change(screen.getByRole('textbox', { name: 'Your email' }), {
            target: { value: 'bot@example.com' },
        });
        fireEvent.change(screen.getByRole('textbox', { name: 'Message' }), { target: { value: 'Cheap pills' } });
        fireEvent.click(screen.getByRole('checkbox'));
        fireEvent.change(honeyPotInput as HTMLInputElement, { target: { value: 'Cheap pills' } });

        fireEvent.click(screen.getByRole('button', { name: 'Send message' }));

        await waitFor(() => {
            expect(contactFormMock).toHaveBeenCalledWith({
                input: expect.objectContaining({ subject: 'Cheap pills' }),
            });
        });
    });

    test('sends the empty hidden field of a real visitor', async () => {
        render(<ContactContent />);

        fireEvent.change(screen.getByRole('textbox', { name: 'Your name' }), { target: { value: 'Customer' } });
        fireEvent.change(screen.getByRole('textbox', { name: 'Your email' }), {
            target: { value: 'customer@example.com' },
        });
        fireEvent.change(screen.getByRole('textbox', { name: 'Message' }), { target: { value: 'Hello' } });
        fireEvent.click(screen.getByRole('checkbox'));

        fireEvent.click(screen.getByRole('button', { name: 'Send message' }));

        await waitFor(() => {
            expect(contactFormMock).toHaveBeenCalledWith({
                input: expect.objectContaining({ subject: '' }),
            });
        });
    });
});
