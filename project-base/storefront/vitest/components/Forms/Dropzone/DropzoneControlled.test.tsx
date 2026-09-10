import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { DropzoneControlled } from 'components/Forms/Dropzone/DropzoneControlled';
import { StrictMode } from 'react';
import { useForm } from 'react-hook-form';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

type FormValues = { files: File[] };

const UploadForm = ({ showPreviews, onSubmit }: { showPreviews: boolean; onSubmit: (values: FormValues) => void }) => {
    const { control, handleSubmit } = useForm<FormValues>({ defaultValues: { files: [] } });

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <DropzoneControlled
                control={control}
                formName="upload"
                label="Attach photos"
                name="files"
                render={(dropzone) => <>{dropzone}</>}
                showPreviews={showPreviews}
            />
            <button type="submit">Submit</button>
        </form>
    );
};

describe('DropzoneControlled', () => {
    beforeEach(() => {
        // JSDOM cannot decode images; exercise file selection without generating thumbnails.
        vi.stubGlobal('createImageBitmap', vi.fn().mockRejectedValue(new Error('Image decoding unavailable')));
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test.each([
        false,
        true,
    ])('adds, deduplicates and removes files without render-time updates (previews: %s)', async (showPreviews) => {
        const user = userEvent.setup();
        const consoleError = vi.spyOn(console, 'error');
        const onSubmit = vi.fn();
        const firstFile = new File(['first'], 'first.jpg', { type: 'image/jpeg', lastModified: 1 });
        const secondFile = new File(['second'], 'second.jpg', { type: 'image/jpeg', lastModified: 2 });
        const duplicateFirstFile = new File(['first'], 'first.jpg', { type: 'image/jpeg', lastModified: 1 });
        const { container } = render(
            <StrictMode>
                <UploadForm showPreviews={showPreviews} onSubmit={onSubmit} />
            </StrictMode>,
        );
        const input = container.querySelector<HTMLInputElement>('input[type="file"]')!;

        await user.upload(input, firstFile);
        await waitFor(() => expect(screen.getAllByRole('listitem')).toHaveLength(1));
        await user.upload(input, secondFile);
        await waitFor(() => expect(screen.getAllByRole('listitem')).toHaveLength(2));
        await user.upload(input, duplicateFirstFile);
        await user.click(screen.getAllByRole('button', { name: 'Remove file' })[0]);
        await user.click(screen.getByRole('button', { name: 'Submit' }));

        await waitFor(() => expect(onSubmit).toHaveBeenCalledOnce());
        expect(onSubmit.mock.calls[0][0].files).toEqual([secondFile]);
        expect(screen.getAllByRole('listitem')).toHaveLength(1);
        expect(consoleError).not.toHaveBeenCalled();
    });
});
