import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { validateImageFile, validateOptionalImageFiles } from 'components/Forms/validationRules';
import { Translate } from 'next-translate';
import { describe, expect, test, vi } from 'vitest';

const createMockT = () => vi.fn((key: string) => key) as unknown as Translate;
const createFile = (size: number): File => ({ size }) as File;

describe('image file validation', () => {
    test('rejects optional image files whose total size exceeds the limit', async () => {
        const schema = validateOptionalImageFiles(createMockT(), VALIDATION_CONSTANTS.reviewMaxFilesCount);
        const files = [
            createFile(VALIDATION_CONSTANTS.fileMaxSize),
            createFile(VALIDATION_CONSTANTS.fileMaxSize),
            createFile(VALIDATION_CONSTANTS.fileMaxSize),
            createFile(1),
        ];

        await expect(schema.isValid(files)).resolves.toBe(false);
    });

    test('accepts required image files whose total size is within the limit', async () => {
        const schema = validateImageFile(createMockT());
        const files = [
            createFile(VALIDATION_CONSTANTS.fileMaxSize),
            createFile(VALIDATION_CONSTANTS.fileMaxSize),
            createFile(VALIDATION_CONSTANTS.fileMaxSize),
        ];

        await expect(schema.isValid(files)).resolves.toBe(true);
    });
});
