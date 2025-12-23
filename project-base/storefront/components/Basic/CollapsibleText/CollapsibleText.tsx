import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { RefObject, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type CollapsibleTextProps = {
    text: string;
    scrollTargetRef: RefObject<HTMLDivElement>;
};

export const CollapsibleText: FC<CollapsibleTextProps> = ({ text, scrollTargetRef }) => {
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
                    scrollTargetRef.current.style.scrollMarginTop = '116px';
                    scrollTargetRef.current.scrollIntoView({ behavior: 'smooth' });
                }
            }

            return !prev;
        });
    };

    return (
        <div className="flex w-full flex-col items-start gap-2">
            <div className={twJoin('relative max-w-2xl', showFullDescription ? '' : 'line-clamp-4')} ref={textRef}>
                <div
                    dangerouslySetInnerHTML={{ __html: text }}
                    className={twJoin(
                        'user-text',
                        !showFullDescription &&
                            shouldShowButton &&
                            "after:from-background-default after:absolute after:bottom-0 after:left-0 after:h-6 after:w-full after:bg-linear-to-t/srgb after:to-transparent after:content-['']",
                    )}
                />
            </div>

            <button
                tabIndex={0}
                className={twJoin(
                    'hover:text-text-accent cursor-pointer underline',
                    showFullDescription && 'mt-2',
                    !shouldShowButton && 'invisible',
                )}
                onClick={handleButtonClick}
            >
                {showFullDescription ? t('Close full description') : t('Open full description')}
                <ArrowSecondaryIcon
                    className={twJoin('text-text-disabled ml-2 size-3', showFullDescription && 'rotate-180')}
                />
            </button>
        </div>
    );
};
