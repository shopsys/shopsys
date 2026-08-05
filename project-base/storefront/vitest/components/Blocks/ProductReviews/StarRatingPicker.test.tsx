import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { StarRatingPicker } from 'components/Blocks/ProductReviews/StarRatingPicker';
import { useState } from 'react';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const StatefulStarRatingPicker = () => {
    const [rating, setRating] = useState(0);

    return (
        <>
            <StarRatingPicker value={rating} onChange={setRating} />
            <button type="button">After rating</button>
        </>
    );
};

describe('StarRatingPicker', () => {
    test('provides a focusable target for form error navigation', () => {
        render(<StarRatingPicker id="review-form-rating" value={0} onChange={vi.fn()} />);

        expect(screen.getByRole('radiogroup', { name: 'Rating 1 to 5 stars' })).toHaveAttribute(
            'id',
            'review-form-rating',
        );
        expect(screen.getByRole('radiogroup', { name: 'Rating 1 to 5 stars' })).toHaveAttribute('tabindex', '-1');
    });

    test('selects stars with arrow keys and exposes a single tab stop', async () => {
        const user = userEvent.setup();
        render(<StatefulStarRatingPicker />);
        const ratingOptions = screen.getAllByRole('radio');
        const oneStar = ratingOptions[0];
        const twoStars = ratingOptions[1];

        await user.tab();
        await user.keyboard('{ArrowRight}');

        expect(twoStars).toHaveFocus();
        expect(twoStars).toHaveAttribute('aria-checked', 'true');
        expect(oneStar).toHaveAttribute('aria-checked', 'false');

        await user.tab();

        expect(screen.getByRole('button', { name: 'After rating' })).toHaveFocus();
    });
});
