import { render, screen } from '@testing-library/react';
import { ContactInformationFormContent } from 'components/Pages/Order/ContactInformation/ContactInformationFormContent';
import { describe, expect, test, vi } from 'vitest';

const { transportTypeCodeMock } = vi.hoisted(() => ({
    transportTypeCodeMock: { value: 'common' },
}));

vi.mock('components/Forms/TextInput/TextInputControlled', () => ({
    TextInputControlled: () => <span>Note input</span>,
}));

vi.mock('components/Pages/Order/ContactInformation/contactInformationFormMeta', () => ({
    useContactInformationFormMeta: () => ({
        fields: { note: { label: 'Note', name: 'note' } },
        formName: 'contact-information-form',
    }),
}));

vi.mock('components/Pages/Order/ContactInformation/FormBlocks/ContactInformationPersonalInformation', () => ({
    ContactInformationPersonalInformation: () => <span>Personal information</span>,
}));

vi.mock('components/Pages/Order/ContactInformation/FormBlocks/ContactInformationBillingAddress', () => ({
    ContactInformationBillingAddress: () => <span>Billing address</span>,
}));

vi.mock('components/Pages/Order/ContactInformation/FormBlocks/ContactInformationDeliveryAddress', () => ({
    ContactInformationDeliveryAddress: () => <span>Delivery address</span>,
}));

vi.mock('react-hook-form', () => ({
    useFormContext: () => ({ control: {}, getValues: vi.fn() }),
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: () => vi.fn(),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ transport: { transportTypeCode: transportTypeCodeMock.value } }),
}));

describe('ContactInformationFormContent', () => {
    test('hides the delivery address section for email transport', () => {
        transportTypeCodeMock.value = 'email';

        render(<ContactInformationFormContent />);

        expect(screen.queryByText('Delivery address')).not.toBeInTheDocument();
        expect(screen.getByText('Billing address')).toBeInTheDocument();
    });

    test('shows the delivery address section for regular transport', () => {
        transportTypeCodeMock.value = 'common';

        render(<ContactInformationFormContent />);

        expect(screen.getByText('Delivery address')).toBeInTheDocument();
    });
});
