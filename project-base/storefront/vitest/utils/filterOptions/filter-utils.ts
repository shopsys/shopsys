import { screen, waitFor } from '@testing-library/react';
import { expect } from 'vitest';

export const waitForFilterApplication = async (timeout: number = 3000) => {
    await waitFor(
        () => {
            expect(screen.queryByRole('progressbar')).not.toBeInTheDocument();
        },
        { timeout },
    );
};

export const mockFilterData = {
    brands: [
        { name: 'Apple', count: 25 },
        { name: 'Samsung', count: 18 },
        { name: 'Sony', count: 12 },
        { name: 'LG', count: 8 },
    ],
    flags: [
        { name: 'Sale', count: 45 },
        { name: 'New', count: 23 },
        { name: 'Popular', count: 67 },
    ],
    parameters: [
        {
            name: 'Color',
            values: [
                { name: 'Red', count: 15 },
                { name: 'Blue', count: 22 },
                { name: 'Black', count: 33 },
                { name: 'White', count: 28 },
            ],
        },
        {
            name: 'Size',
            values: [
                { name: 'Small', count: 12 },
                { name: 'Medium', count: 25 },
                { name: 'Large', count: 18 },
                { name: 'X-Large', count: 9 },
            ],
        },
    ],
    priceRange: {
        min: 0,
        max: 1000,
        currentMin: 50,
        currentMax: 750,
    },
};
