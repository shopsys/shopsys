import { StarIcon } from 'components/Basic/Icon/StarIcon';
import { KeyboardEvent, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

const STAR_COUNT = 5;

type StarRatingPickerProps = {
    id?: string;
    value: number;
    onChange: (rating: number) => void;
    isDisabled?: boolean;
};

export const StarRatingPicker: FC<StarRatingPickerProps> = ({ id, value, onChange, isDisabled = false }) => {
    const { t } = useTranslation();
    const [hoveredRating, setHoveredRating] = useState(0);
    const starButtonRefs = useRef<Array<HTMLButtonElement | null>>([]);

    const displayedRating = hoveredRating || value;

    const handleKeyDown = (event: KeyboardEvent<HTMLButtonElement>, rating: number) => {
        let nextRating: number;

        switch (event.key) {
            case 'ArrowDown':
            case 'ArrowRight':
                nextRating = (rating % STAR_COUNT) + 1;
                break;
            case 'ArrowLeft':
            case 'ArrowUp':
                nextRating = ((rating - 2 + STAR_COUNT) % STAR_COUNT) + 1;
                break;
            default:
                return;
        }

        event.preventDefault();
        starButtonRefs.current[nextRating - 1]?.focus();
        onChange(nextRating);
    };

    return (
        <div
            id={id}
            aria-label={t('Rating 1 to 5 stars', { ns: 'accessibility' })}
            className="flex items-center gap-1"
            role="radiogroup"
            tabIndex={-1}
            onMouseLeave={() => setHoveredRating(0)}
        >
            {Array.from({ length: STAR_COUNT }, (_, index) => {
                const rating = index + 1;

                return (
                    /* biome-ignore lint/a11y/useSemanticElements: The star buttons paint a shared hover preview across the whole group, which native radio inputs cannot express without losing the button semantics screen readers get from the radiogroup pattern. */
                    <button
                        key={rating}
                        aria-checked={value === rating}
                        className="cursor-pointer rounded-sm p-0.5 disabled:cursor-not-allowed"
                        disabled={isDisabled}
                        ref={(element) => {
                            starButtonRefs.current[index] = element;
                        }}
                        role="radio"
                        tabIndex={!isDisabled && (value === rating || (value === 0 && rating === 1)) ? 0 : -1}
                        type="button"
                        aria-label={t('{{ count }} stars', {
                            ns: 'accessibility',
                            count: rating,
                        })}
                        onClick={() => onChange(rating)}
                        onKeyDown={(event) => handleKeyDown(event, rating)}
                        onMouseEnter={() => setHoveredRating(rating)}
                    >
                        <StarIcon
                            fill={rating <= displayedRating ? 'currentColor' : 'none'}
                            className={twMergeCustom(
                                'size-8 text-gray-700',
                                rating <= displayedRating && 'text-orange-500',
                                isDisabled && 'text-gray-300',
                                isDisabled && rating <= displayedRating && 'text-orange-500',
                            )}
                        />
                    </button>
                );
            })}
        </div>
    );
};
