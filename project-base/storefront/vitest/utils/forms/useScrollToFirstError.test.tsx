import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Form } from 'components/Forms/Form/Form';
import { FormProvider } from 'react-hook-form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { object, string } from 'yup';

type FormValues = {
    email: string;
    firstName: string;
};

const scrollIntoViewMock = vi.fn();

const TestForm: FC = () => {
    const formProviderMethods = useFormWrapper(
        yupResolver<FormValues>(
            object({
                email: string().required('Email is required'),
                firstName: string().required('First name is required'),
            }),
        ),
        { email: '', firstName: '' },
    );

    return (
        <FormProvider {...formProviderMethods}>
            <Form formName="test-form" onSubmit={formProviderMethods.handleSubmit(vi.fn())}>
                <input id="test-form-email" aria-label="Email" {...formProviderMethods.register('email')} />
                <input
                    id="test-form-firstName"
                    aria-label="First name"
                    {...formProviderMethods.register('firstName')}
                />
                <button type="submit">Submit</button>
            </Form>
        </FormProvider>
    );
};

describe('useScrollToFirstError', () => {
    beforeEach(() => {
        scrollIntoViewMock.mockClear();
        window.HTMLElement.prototype.scrollIntoView = scrollIntoViewMock;
    });

    test('does not move focus while correcting fields after an invalid submit', async () => {
        const user = userEvent.setup();
        render(<TestForm />);

        await user.click(screen.getByRole('button', { name: 'Submit' }));
        await waitFor(() => {
            expect(screen.getByRole('textbox', { name: 'Email' })).toHaveFocus();
            expect(scrollIntoViewMock).toHaveBeenCalledOnce();
        });

        const firstNameInput = screen.getByRole('textbox', { name: 'First name' });
        await user.type(firstNameInput, 'John');

        expect(firstNameInput).toHaveValue('John');
        expect(firstNameInput).toHaveFocus();
        expect(scrollIntoViewMock).toHaveBeenCalledOnce();
    });
});
