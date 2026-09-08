import { fireEvent, render, screen } from '@testing-library/react';
import { ProductComparisonBody } from 'components/Pages/ProductComparison/ProductComparisonBody';
import { ComparisonParameter } from 'utils/productLists/comparison/getComparisonParameters';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
const parameters: ComparisonParameter[] = [
    { uuid: 'color', name: 'Color', group: 'Appearance', unit: null, values: [['Blue'], ['Blue']], isDifferent: false },
    {
        uuid: 'ports',
        name: 'Ports',
        group: 'Connectivity',
        unit: null,
        values: [null, ['USB', 'HDMI']],
        isDifferent: true,
    },
];

describe('ProductComparisonBody', () => {
    test('hides equal rows and their empty group when showing differences', () => {
        render(
            <table>
                <ProductComparisonBody onlyDifferences parameters={parameters} productCount={2} />
            </table>,
        );

        expect(screen.queryByText('Color')).not.toBeInTheDocument();
        expect(screen.queryByRole('button', { name: /Appearance/ })).not.toBeInTheDocument();
        expect(screen.getByText('USB, HDMI')).toBeInTheDocument();
        expect(screen.getByText('Not specified')).toBeInTheDocument();
    });

    test('collapses and reopens a parameter group', () => {
        render(
            <table>
                <ProductComparisonBody onlyDifferences={false} parameters={parameters} productCount={2} />
            </table>,
        );
        const toggle = screen.getByRole('button', { name: /Connectivity/ });

        fireEvent.click(toggle);

        expect(toggle).toHaveAttribute('aria-expanded', 'false');
        expect(screen.queryByText('USB, HDMI')).not.toBeInTheDocument();
        expect(screen.getAllByText('Blue')).toHaveLength(2);

        fireEvent.click(toggle);

        expect(screen.getByText('USB, HDMI')).toBeInTheDocument();
    });

    test('reports unavailable data instead of equal parameters when the differences filter is active', () => {
        render(
            <table>
                <ProductComparisonBody onlyDifferences parameters={[]} productCount={2} />
            </table>,
        );
        expect(screen.getByText('No parameters are available for these products.')).toBeInTheDocument();
        expect(screen.queryByText(/same parameters/)).not.toBeInTheDocument();
    });

    test('explains why no rows remain when all parameters match', () => {
        render(
            <table>
                <ProductComparisonBody onlyDifferences parameters={[parameters[0]]} productCount={2} />
            </table>,
        );

        expect(
            screen.getByText('The selected products have the same parameters. Turn off the filter to see all values.'),
        ).toBeInTheDocument();
    });
});
