import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { type RefObject, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type CollapsibleTextProps = {
    text: string;
    scrollTargetRef: RefObject<HTMLDivElement | null>;
    textClassName?: string;
};

export const CollapsibleText: FC<CollapsibleTextProps> = ({ text, scrollTargetRef, textClassName }) => {
    const { t } = useTranslation();
    const [showFullDescription, setShowFullDescription] = useState(false);
    const [shouldShowButton, setShouldShowButton] = useState(false);
    const textRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (textRef.current) {
            const { scrollHeight, clientHeight } = textRef.current;
            setShouldShowButton(scrollHeight > clientHeight);
        }
    }, [text]);

    const handleButtonClick = () => {
        setShowFullDescription((prev) => {
            if (prev) {
                if (scrollTargetRef.current) {
                    scrollTargetRef.current.scrollIntoView({ behavior: 'smooth' });
                }
            }

            return !prev;
        });
    };

    return (
        <div className="flex w-full flex-col items-start gap-2">
            <div
                className={twMergeCustom('relative max-w-2xl', !showFullDescription && 'line-clamp-4', textClassName)}
                ref={textRef}
            >
                <div
                    dangerouslySetInnerHTML={{ __html: text }}
                    className={twJoin(
                        'user-text',
                        !showFullDescription &&
                            shouldShowButton &&
                            "after:absolute after:bottom-0 after:left-0 after:h-6 after:w-full after:bg-linear-to-t/srgb after:from-background-default after:to-transparent after:content-['']",
                    )}
                />
            </div>

            <button
                tabIndex={0}
                className={twJoin(
                    'cursor-pointer underline hover:text-text-accent',
                    showFullDescription && 'mt-2',
                    !shouldShowButton && 'invisible',
                )}
                onClick={handleButtonClick}
            >
                {showFullDescription ? t('Close full description') : t('Open full description')}
                <ArrowSecondaryIcon
                    className={twJoin('ml-2 size-3 text-text-disabled', showFullDescription && 'rotate-180')}
                />
            </button>
        </div>
    );
};
