import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Form } from 'components/Forms/Form/Form';
import { FormProvider } from 'react-hook-form';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { object, string } from 'yup';

const { updatePortalContentMock } = vi.hoisted(() => ({
    updatePortalContentMock: vi.fn(),
}));

vi.mock('next/dynamic', () => ({
    default: () => () => null,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: any) => selector({ updatePortalContent: updatePortalContentMock }),
}));

type FormValues = {
    email: string;
};

const fields = { email: { name: 'email', label: 'Email' } };

const REQUIRED_ERROR = 'Please enter email';
const INVALID_ERROR = 'Please enter a valid email';

const onValidSubmitMock = vi.fn();

const TestForm: FC = () => {
    const formProviderMethods = useFormWrapper(
        yupResolver<FormValues>(
            object({
                email: string().email(INVALID_ERROR).required(REQUIRED_ERROR),
            }),
        ),
        { email: '' },
    );

    useErrorPopup(formProviderMethods, fields);

    return (
        <FormProvider {...formProviderMethods}>
            <Form formName="test-form" onSubmit={formProviderMethods.handleSubmit(onValidSubmitMock)}>
                <input id="test-form-email" aria-label="Email" {...formProviderMethods.register('email')} />
                <span>{formProviderMethods.formState.errors.email?.message}</span>
                <span data-testid="submit-count">{formProviderMethods.formState.submitCount}</span>
                <button type="submit">Submit</button>
            </Form>
        </FormProvider>
    );
};

describe('useErrorPopup', () => {
    beforeEach(() => {
        updatePortalContentMock.mockClear();
        window.HTMLElement.prototype.scrollIntoView = vi.fn();
    });

    test('does not open the popup again while the invalid field is being corrected', async () => {
        const user = userEvent.setup();
        render(<TestForm />);

        await user.click(screen.getByRole('button', { name: 'Submit' }));
        await waitFor(() => {
            expect(screen.getByText(REQUIRED_ERROR)).toBeInTheDocument();
            expect(updatePortalContentMock).toHaveBeenCalledOnce();
        });

        await user.type(screen.getByRole('textbox', { name: 'Email' }), 'no-reply');

        await waitFor(() => expect(screen.getByText(INVALID_ERROR)).toBeInTheDocument());
        expect(updatePortalContentMock).toHaveBeenCalledOnce();
    });

    test('does not open the popup when the submit succeeds', async () => {
        const user = userEvent.setup();
        render(<TestForm />);

        await user.type(screen.getByRole('textbox', { name: 'Email' }), 'no-reply@shopsys.com');
        await user.click(screen.getByRole('button', { name: 'Submit' }));

        // waits for the submitCount bump, so the popup effect has already run by the time we assert
        await waitFor(() => expect(screen.getByTestId('submit-count')).toHaveTextContent('1'));
        expect(onValidSubmitMock).toHaveBeenCalledOnce();
        expect(updatePortalContentMock).not.toHaveBeenCalled();
    });

    test('opens the popup on every submit attempt', async () => {
        const user = userEvent.setup();
        render(<TestForm />);

        await user.click(screen.getByRole('button', { name: 'Submit' }));
        await waitFor(() => expect(updatePortalContentMock).toHaveBeenCalledOnce());

        await user.click(screen.getByRole('button', { name: 'Submit' }));
        await waitFor(() => expect(updatePortalContentMock).toHaveBeenCalledTimes(2));
    });
});
