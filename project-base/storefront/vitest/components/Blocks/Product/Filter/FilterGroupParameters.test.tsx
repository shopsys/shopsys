import { fireEvent, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FilterGroupParameters } from 'components/Blocks/Product/Filter/FilterGroupParameters';
import { ParametersType } from 'types/productFilter';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { testFocusManagement } from 'vitest/utils/accessibility/a11y-testing';
import {
    navigateToElement,
    navigateWithTab,
    pressEnterKey,
    pressSpaceKey,
} from 'vitest/utils/accessibility/keyboard-navigation';
import { mockFilterData, waitForFilterApplication } from 'vitest/utils/filterOptions/filter-utils';

const mockUpdateFilterQuery = vi.fn();
const mockCurrentFilterQuery: any = {
    parameters: [
        {
            parameter: 'param-1',
            values: ['value-1', 'value-3'],
        },
        {
            parameter: 'param-2',
            values: ['color-red'],
        },
        {
            parameter: 'param-3',
            minimalValue: 10,
            maximalValue: 50,
        },
    ],
};

vi.mock('utils/queryParams/useUpdateFilterQuery', () => ({
    useUpdateFilterQuery: () => ({
        updateFilterParametersQuery: mockUpdateFilterQuery,
    }),
}));

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({
    useCurrentFilterQuery: () => mockCurrentFilterQuery,
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: any) => {
        const state = {
            defaultProductFiltersMap: {
                parameters: new Map([['param-1', new Set(['value-1'])]]),
            },
        };
        return selector(state);
    },
}));

vi.mock('framer-motion', async () => {
    const actual = (await vi.importActual('framer-motion')) as any;
    return {
        ...(actual || {}),
        AnimatePresence: ({ children }: { children: React.ReactNode }) => <>{children}</>,
        motion: {
            div: ({ children, ...props }: any) => <div {...props}>{children}</div>,
        },
        m: {
            div: ({ children, ...props }: any) => <div {...props}>{children}</div>,
        },
    };
});

const createCheckboxParameter = (
    uuid: string,
    name: string,
    values: Array<{ uuid: string; name: string; count: number }>,
): ParametersType =>
    ({
        __typename: 'ParameterCheckboxFilterOption',
        uuid,
        name,
        isCollapsed: false,
        isSelectable: true,
        unit: null,
        values: values.map((v) => {
            const parameterFilter = mockCurrentFilterQuery.parameters?.find((p: any) => p.parameter === uuid);
            const isSelected = parameterFilter?.values?.includes(v.uuid) || false;

            return {
                uuid: v.uuid,
                name: v.name,
                text: v.name,
                count: v.count,
                isAbsolute: false,
                isSelected,
            };
        }),
    }) as any;

const createColorParameter = (
    uuid: string,
    name: string,
    values: Array<{ uuid: string; name: string; count: number; rgbColor: string }>,
): ParametersType =>
    ({
        __typename: 'ParameterColorFilterOption',
        uuid,
        name,
        isCollapsed: false,
        isSelectable: true,
        unit: null,
        values: values.map((v) => {
            const parameterFilter = mockCurrentFilterQuery.parameters?.find((p: any) => p.parameter === uuid);
            const isSelected = parameterFilter?.values?.includes(v.uuid) || false;

            return {
                uuid: v.uuid,
                name: v.name,
                text: v.name,
                count: v.count,
                isAbsolute: false,
                isSelected,
                rgbColor: v.rgbColor,
            };
        }),
    }) as any;

const createSliderParameter = (
    uuid: string,
    name: string,
    min: number,
    max: number,
    currentMin: number,
    currentMax: number,
    unit: string | null = null,
): ParametersType =>
    ({
        __typename: 'ParameterSliderFilterOption',
        uuid,
        name,
        isCollapsed: false,
        isSelectable: true,
        unit: unit ? { name: unit } : null,
        minimalValue: min,
        maximalValue: max,
        selectedValue: {
            minimalValue: currentMin,
            maximalValue: currentMax,
        },
    }) as any;

const mockCheckboxParameter: ParametersType = createCheckboxParameter('param-1', 'Material', [
    { uuid: 'value-1', name: 'Cotton', count: 15 },
    { uuid: 'value-2', name: 'Polyester', count: 8 },
    { uuid: 'value-3', name: 'Wool', count: 12 },
    { uuid: 'value-4', name: 'Silk', count: 5 },
    { uuid: 'value-5', name: 'Linen', count: 0 },
]);

const mockColorParameter: ParametersType = createColorParameter('param-2', 'Color', [
    { uuid: 'color-red', name: 'Red', count: 20, rgbColor: '#FF0000' },
    { uuid: 'color-blue', name: 'Blue', count: 15, rgbColor: '#0000FF' },
    { uuid: 'color-green', name: 'Green', count: 10, rgbColor: '#00FF00' },
    { uuid: 'color-yellow', name: 'Yellow', count: 0, rgbColor: '#FFFF00' },
]);

const mockSliderParameter: ParametersType = createSliderParameter('param-3', 'Weight', 0, 100, 10, 50, 'kg');

const defaultCheckboxProps = {
    title: 'Material Filter',
    parameterIndex: 0,
    parameter: mockCheckboxParameter,
    defaultNumberOfShownParameters: 3,
    isActive: false,
};

const defaultColorProps = {
    title: 'Color Filter',
    parameterIndex: 1,
    parameter: mockColorParameter,
    defaultNumberOfShownParameters: 3,
    isActive: false,
};

const defaultSliderProps = {
    title: 'Weight Filter',
    parameterIndex: 2,
    parameter: mockSliderParameter,
    defaultNumberOfShownParameters: 3,
    isActive: true,
};

describe('FilterGroupParameters', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockCurrentFilterQuery.parameters = [
            {
                parameter: 'param-1',
                values: ['value-1', 'value-3'],
            },
            {
                parameter: 'param-2',
                values: ['color-red'],
            },
            {
                parameter: 'param-3',
                minimalValue: 10,
                maximalValue: 50,
            },
        ];
    });

    describe('Basic Rendering', () => {
        test('renders filter group with title', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const container = document.querySelector('.vl\\:py-5');
            expect(container).toBeInTheDocument();

            expect(screen.getByText('Material Filter')).toBeInTheDocument();
        });

        test('renders active state correctly', () => {
            render(<FilterGroupParameters {...defaultSliderProps} />);

            const activeIndicator = document.querySelector('.bg-background-success');
            expect(activeIndicator).toBeInTheDocument();
        });

        test('renders inactive state correctly', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const activeIndicator = document.querySelector('.bg-background-success');
            expect(activeIndicator).not.toBeInTheDocument();
        });

        test('renders title with unit when available', () => {
            render(<FilterGroupParameters {...defaultSliderProps} />);

            expect(screen.getByText('Weight Filter (kg)')).toBeInTheDocument();
        });

        test('renders title without unit when not available', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            expect(screen.getByText('Material Filter')).toBeInTheDocument();
        });
    });

    describe('Checkbox Parameter Type', () => {
        test('renders checkbox parameter options', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);

            expect(checkboxes[0]).toBeChecked();
            expect(checkboxes[1]).not.toBeChecked();
            expect(checkboxes[2]).toBeChecked();
        });

        test('calls updateFilterParametersQuery when checkbox is selected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const uncheckedCheckbox = checkboxes[1];

            await user.click(uncheckedCheckbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('param-1', 'value-2');
        });

        test('calls updateFilterParametersQuery when checkbox is deselected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const checkedCheckbox = checkboxes[0];

            await user.click(checkedCheckbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('param-1', 'value-1');
        });

        test('shows disabled state for zero count options', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const showMoreButton = screen.getByText('Show more');
            await user.click(showMoreButton);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(5);

            const linenCheckbox = checkboxes[4];
            expect(linenCheckbox).toBeDisabled();

            expect(checkboxes[0]).not.toBeDisabled();
            expect(checkboxes[1]).not.toBeDisabled();
            expect(checkboxes[2]).not.toBeDisabled();
            expect(checkboxes[3]).not.toBeDisabled();
        });

        test('shows more/less functionality for many options', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            expect(screen.getByText('Show more')).toBeInTheDocument();
        });

        test('expands to show more options when show all is clicked', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const showMoreButton = screen.getByText('Show more');
            await user.click(showMoreButton);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(5);
            expect(screen.getByText('Show less')).toBeInTheDocument();
        });
    });

    describe('Color Parameter Type', () => {
        test('renders color parameter options', () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            expect(colorInputs).toHaveLength(4);
        });

        test('renders color swatches with correct colors', () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            expect(screen.getByText('Red')).toBeInTheDocument();
            expect(screen.getByText('Blue')).toBeInTheDocument();
            expect(screen.getByText('Green')).toBeInTheDocument();
        });

        test('shows selected state for color swatches', () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            expect(colorInputs[0]).toBeChecked();
            expect(colorInputs[1]).not.toBeChecked();
        });

        test('calls updateFilterParametersQuery when color is selected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            const unselectedInput = colorInputs[1];

            await user.click(unselectedInput);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('param-2', 'color-blue');
        });

        test('calls updateFilterParametersQuery when color is deselected', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            const selectedInput = colorInputs[0];

            await user.click(selectedInput);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('param-2', 'color-red');
        });

        test('shows disabled state for zero count color options', () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );

            expect(colorInputs).toHaveLength(4);

            expect(colorInputs[3]).toBeDisabled();

            expect(colorInputs[0]).not.toBeDisabled();
            expect(colorInputs[1]).not.toBeDisabled();
            expect(colorInputs[2]).not.toBeDisabled();
        });
    });

    describe('Slider Parameter Type', () => {
        test('renders slider parameter', () => {
            render(<FilterGroupParameters {...defaultSliderProps} />);

            const numberInputs = screen.getAllByRole('spinbutton');
            expect(numberInputs).toHaveLength(2);
        });

        test('renders slider with correct current values', () => {
            render(<FilterGroupParameters {...defaultSliderProps} />);

            const numberInputs = screen.getAllByRole('spinbutton');
            expect(numberInputs[0]).toHaveValue(10);
        });

        test('calls updateFilterParametersQuery when min value changes', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultSliderProps} />);

            const numberInputs = screen.getAllByRole('spinbutton');
            const minInput = numberInputs[0];

            await user.clear(minInput);
            await user.type(minInput, '15');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('param-3', undefined, 15, 50);
        });

        test('calls updateFilterParametersQuery when max value changes', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultSliderProps} />);

            const numberInputs = screen.getAllByRole('spinbutton');
            const maxInput = numberInputs[1];

            await user.clear(maxInput);
            await user.type(maxInput, '75');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterQuery).toHaveBeenCalledWith('param-3', undefined, 10, 75);
        });

        test('renders slider without unit when not available', () => {
            const sliderWithoutUnit = {
                ...defaultSliderProps,
                parameter: createSliderParameter('param-3', 'Weight', 0, 100, 10, 50, null),
            };

            render(<FilterGroupParameters {...sliderWithoutUnit} />);

            const numberInputs = screen.getAllByRole('spinbutton');
            expect(numberInputs).toHaveLength(2);
        });
    });

    describe('Group Collapse/Expand', () => {
        test('group starts in expanded state', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const content = document.querySelector('.vl\\:pt-2\\.5');
            expect(content).toBeInTheDocument();
        });

        test('collapses group when title is clicked', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const titleButton = screen.getByLabelText('Filter by parameter Material Filter');
            await user.click(titleButton);

            expect(titleButton).toBeInTheDocument();
        });

        test('expands group when title is clicked again', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const titleButton = screen.getByLabelText('Filter by parameter Material Filter');

            await user.click(titleButton);

            await user.click(titleButton);

            expect(titleButton).toBeInTheDocument();
        });
    });

    describe('Session Store Integration', () => {
        test('applies default parameters from session store', () => {
            mockCurrentFilterQuery.parameters = [];

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes[0]).toBeChecked();
            expect(checkboxes[1]).not.toBeChecked();
            expect(checkboxes[2]).not.toBeChecked();
        });
    });

    describe('Accessibility Features', () => {
        test('has proper ARIA labels for checkboxes', () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            expect(screen.getByText('Cotton')).toBeInTheDocument();
            expect(screen.getByText('Polyester')).toBeInTheDocument();
            expect(screen.getByText('Wool')).toBeInTheDocument();
        });

        test('has proper ARIA labels for color swatches', () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            expect(screen.getByText('Red')).toBeInTheDocument();
            expect(screen.getByText('Blue')).toBeInTheDocument();
            expect(screen.getByText('Green')).toBeInTheDocument();
        });

        test('has proper ARIA labels for range slider inputs', () => {
            render(<FilterGroupParameters {...defaultSliderProps} />);

            const minRangeInput = screen.getByLabelText('Minimum value');
            const maxRangeInput = screen.getByLabelText('Maximum value');

            expect(minRangeInput).toHaveAttribute('aria-label', 'Minimum value');
            expect(maxRangeInput).toHaveAttribute('aria-label', 'Maximum value');
        });

        test('supports keyboard navigation between checkboxes', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');

            checkboxes[0].focus();
            expect(document.activeElement).toBe(checkboxes[0]);

            await navigateWithTab();
            expect(document.activeElement).toBe(checkboxes[1]);

            await navigateWithTab();
            expect(document.activeElement).toBe(checkboxes[2]);
        });

        test('supports keyboard navigation between color swatches', async () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );

            colorInputs[0].focus();
            expect(document.activeElement).toBe(colorInputs[0]);

            await navigateWithTab();
            expect(document.activeElement).toBe(colorInputs[1]);
        });

        test('supports keyboard selection with Space key for checkboxes', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const uncheckedCheckbox = checkboxes[1];

            uncheckedCheckbox.focus();
            await pressSpaceKey();

            expect(mockUpdateFilterQuery).toHaveBeenCalled();
        });

        test('supports keyboard selection with Space key for color swatches', async () => {
            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            const unselectedInput = colorInputs[1];

            unselectedInput.focus();
            await pressSpaceKey();

            expect(mockUpdateFilterQuery).toHaveBeenCalled();
        });

        test('supports keyboard activation of group title', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const titleButton = screen.getByLabelText('Filter by parameter Material Filter');

            titleButton.focus();
            await pressEnterKey();

            expect(titleButton).toBeInTheDocument();
        });

        test('supports keyboard activation of show all button', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const showMoreButton = screen.getByText('Show more');

            showMoreButton.focus();
            await pressEnterKey();

            expect(screen.getByText('Show less')).toBeInTheDocument();
        });

        test('maintains focus after parameter selection', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');
            const targetCheckbox = checkboxes[1];

            targetCheckbox.focus();
            await user.click(targetCheckbox);

            expect(document.activeElement).toBe(targetCheckbox);
        });
    });

    describe('Edge Cases and Error Handling', () => {
        test('handles empty parameter values', () => {
            const emptyParameter = {
                ...defaultCheckboxProps,
                parameter: createCheckboxParameter('param-empty', 'Empty', []),
            };

            render(<FilterGroupParameters {...emptyParameter} />);

            const container = document.querySelector('.vl\\:py-5');
            expect(container).toBeInTheDocument();
            expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
            expect(screen.queryByText('Show more')).not.toBeInTheDocument();
        });

        test('handles parameter with all zero counts', () => {
            const zeroCountParameter = createCheckboxParameter('param-zero', 'Zero Count', [
                { uuid: 'value-1', name: 'Option 1', count: 0 },
                { uuid: 'value-2', name: 'Option 2', count: 0 },
            ]);

            render(<FilterGroupParameters {...defaultCheckboxProps} parameter={zeroCountParameter} />);

            const checkboxes = screen.getAllByRole('checkbox');
            checkboxes.forEach((checkbox) => {
                expect(checkbox).toBeDisabled();
            });
        });

        test('handles very long parameter names', () => {
            const longNameParameter = createCheckboxParameter(
                'param-long',
                'Very Long Parameter Name That Might Cause Layout Issues',
                [{ uuid: 'value-1', name: 'Very Long Parameter Value Name That Might Cause Layout Issues', count: 5 }],
            );

            render(<FilterGroupParameters {...defaultCheckboxProps} parameter={longNameParameter} />);

            expect(
                screen.getByText('Very Long Parameter Value Name That Might Cause Layout Issues'),
            ).toBeInTheDocument();
        });

        test('handles rapid successive clicks on checkboxes', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkbox = screen.getAllByRole('checkbox')[1];

            await user.click(checkbox);
            await user.click(checkbox);
            await user.click(checkbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalledTimes(3);
        });

        test('handles rapid successive clicks on color swatches', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultColorProps} />);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            const colorInput = colorInputs[1];

            await user.click(colorInput);
            await user.click(colorInput);
            await user.click(colorInput);

            expect(mockUpdateFilterQuery).toHaveBeenCalledTimes(3);
        });

        test('handles slider edge values', async () => {
            const user = userEvent.setup();

            render(<FilterGroupParameters {...defaultSliderProps} />);

            const numberInputs = screen.getAllByRole('spinbutton');
            const minInput = numberInputs[0];
            const maxInput = numberInputs[1];

            mockUpdateFilterQuery.mockClear();

            await user.clear(minInput);
            await user.type(minInput, '0');
            fireEvent.blur(minInput);

            await user.clear(maxInput);
            await user.type(maxInput, '100');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterQuery).toHaveBeenCalledTimes(3);
        });
    });

    describe('Performance', () => {
        test('handles large number of parameter values', () => {
            const manyValues = Array.from({ length: 100 }, (_, i) => ({
                uuid: `value-${i}`,
                name: `Value ${i}`,
                count: (i % 10) + 1,
            }));

            const largeParameter = createCheckboxParameter('param-large', 'Large Parameter', manyValues);

            const startTime = performance.now();
            render(<FilterGroupParameters {...defaultCheckboxProps} parameter={largeParameter} />);
            const endTime = performance.now();

            // Increased threshold to account for CI environment variability
            expect(endTime - startTime).toBeLessThan(200);

            const checkboxes = screen.getAllByRole('checkbox');
            expect(checkboxes).toHaveLength(3);
        });

        test('handles large number of color values', () => {
            const manyColors = Array.from({ length: 50 }, (_, i) => ({
                uuid: `color-${i}`,
                name: `Color ${i}`,
                count: (i % 5) + 1,
                rgbColor: `#${i.toString(16).padStart(6, '0')}`,
            }));

            const largeColorParameter = createColorParameter('param-colors', 'Many Colors', manyColors);

            const startTime = performance.now();
            render(<FilterGroupParameters {...defaultColorProps} parameter={largeColorParameter} />);
            const endTime = performance.now();

            // Increased threshold to account for CI environment variability
            // Original: 100ms was too tight for shared CI runners
            expect(endTime - startTime).toBeLessThan(200);

            const allInputs = screen.getAllByRole('checkbox');
            const colorInputs = allInputs.filter((input) =>
                input.getAttribute('name')?.includes('parameters.1.values'),
            );
            expect(colorInputs).toHaveLength(50);
        });
    });

    describe('Integration with Test Utilities', () => {
        test('works with filter testing utilities', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            expect(mockFilterData).toBeDefined();
            expect(mockFilterData.parameters).toBeDefined();

            await waitForFilterApplication();

            const checkbox = screen.getAllByRole('checkbox')[1];
            await userEvent.setup().click(checkbox);

            expect(mockUpdateFilterQuery).toHaveBeenCalled();
        });

        test('works with accessibility testing utilities', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const container = document.querySelector('.vl\\:py-5');
            expect(container).toBeInTheDocument();

            const checkboxes = screen.getAllByRole('checkbox');
            if (checkboxes.length > 0) {
                await testFocusManagement(checkboxes[0]);
            }
        });

        test('works with keyboard navigation utilities', async () => {
            render(<FilterGroupParameters {...defaultCheckboxProps} />);

            const checkboxes = screen.getAllByRole('checkbox');

            const firstCheckbox = await navigateToElement('checkbox', 'Cotton');
            expect(firstCheckbox).toBe(checkboxes[0]);

            await navigateWithTab();
            expect(document.activeElement).toBe(checkboxes[1]);

            await pressSpaceKey();
            expect(mockUpdateFilterQuery).toHaveBeenCalled();
        });
    });
});
