import { StarIcon } from 'components/Basic/Icon/StarIcon';
import { twMergeCustom } from 'utils/twMerge';

const STAR_COUNT = 5;

type ReviewStarsProps = {
    rating: number;
    ariaLabel?: string;
    starClassName?: string;
};

export const ReviewStars: FC<ReviewStarsProps> = ({ rating, ariaLabel, starClassName, className }) => (
    <span
        aria-hidden={ariaLabel === undefined}
        aria-label={ariaLabel}
        className={twMergeCustom('inline-flex items-center gap-0.5', className)}
        role={ariaLabel === undefined ? undefined : 'img'}
    >
        {Array.from({ length: STAR_COUNT }, (_, index) => {
            const fillPercentage = Math.min(Math.max((rating - index) * 100, 0), 100);
            const isFullyFilled = fillPercentage === 100;

            return (
                <span key={index} aria-hidden="true" className="relative flex">
                    <StarIcon
                        className={twMergeCustom(
                            'size-5 text-gray-400',
                            isFullyFilled && 'text-orange-500',
                            starClassName,
                        )}
                        fill={isFullyFilled ? 'currentColor' : 'var(--color-gray-400)'}
                    />

                    {fillPercentage > 0 && !isFullyFilled && (
                        <span className="absolute inset-0 flex overflow-hidden" style={{ width: `${fillPercentage}%` }}>
                            <StarIcon
                                className={twMergeCustom('size-5 shrink-0 text-orange-500', starClassName)}
                                fill="currentColor"
                            />
                        </span>
                    )}
                </span>
            );
        })}
    </span>
);
