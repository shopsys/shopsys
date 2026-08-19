import { render, screen } from '@testing-library/react';
import { NavigationItemContent } from 'components/Layout/Header/Navigation/NavigationItemContent';
import { describe, expect, test } from 'vitest';

describe('NavigationItemContent', () => {
    test('uses only phrasing content when rendered inside a button', () => {
        render(
            <button type="button">
                <NavigationItemContent isDropdownTrigger isMenuOpened={false} name="Electronics" />
            </button>,
        );

        const button = screen.getByRole('button', { name: 'Electronics' });
        expect(button.querySelector('div')).not.toBeInTheDocument();
        expect(button.querySelector('span')).toBeInTheDocument();
    });
});
