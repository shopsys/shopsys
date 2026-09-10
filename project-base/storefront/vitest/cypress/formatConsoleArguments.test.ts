import { formatConsoleArguments } from 'cypress/support/formatConsoleArguments';
import { describe, expect, test } from 'vitest';

describe('formatConsoleArguments', () => {
    test('replaces console format specifiers with their argument values', () => {
        const formattedMessage = formatConsoleArguments([
            'Suppressed GraphQL error during redirect handling: %s',
            'Customer is not authorized.',
        ]);

        expect(formattedMessage).toBe('Suppressed GraphQL error during redirect handling: Customer is not authorized.');
    });

    test('serializes objects and appends arguments without a format specifier', () => {
        const formattedMessage = formatConsoleArguments(['Request failed: %o', { status: 500 }, 'retry disabled']);

        expect(formattedMessage).toBe('Request failed: {\n  "status": 500\n} retry disabled');
    });

    test('preserves format specifiers without a matching argument', () => {
        const formattedMessage = formatConsoleArguments(['Progress: 50%%, detail: %s']);

        expect(formattedMessage).toBe('Progress: 50%, detail: %s');
    });
});
