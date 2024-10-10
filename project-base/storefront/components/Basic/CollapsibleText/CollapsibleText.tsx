import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import DOMPurify from 'isomorphic-dompurify';
import { RefObject, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type CollapsibleTextProps = {
    text: string;
    scrollTargetRef: RefObject<HTMLDivElement>;
};

export const CollapsibleText: FC<CollapsibleTextProps> = ({ text, scrollTargetRef }) => {
    const [showFullDescription, setShowFullDescription] = useState(false);
    const [shouldShowButton, setShouldShowButton] = useState(false);
    const textRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (textRef.current) {
            const { scrollHeight, clientHeight } = textRef.current;
            setShouldShowButton(scrollHeight > clientHeight);
        }
    }, [text]);

    const cleanHtml = DOMPurify.sanitize(text);

    const handleButtonClick = () => {
        setShowFullDescription((prev) => {
            if (prev) {
                scrollTargetRef.current?.scrollIntoView({ behavior: 'smooth' });
            }

            return !prev;
        });
    };

    return (
        <div className="flex w-full flex-col items-start gap-2">
            <div className={twJoin('relative max-w-2xl', showFullDescription ? '' : 'line-clamp-4')} ref={textRef}>
                <div
                    dangerouslySetInnerHTML={{ __html: cleanHtml }}
                    className={twJoin(
                        'user-text',
                        !showFullDescription &&
                            shouldShowButton &&
                            "after:absolute after:bottom-0 after:left-0 after:h-6 after:w-full after:bg-gradient-to-t after:from-background after:to-transparent after:content-['']",
                    )}
                />
            </div>
            {shouldShowButton && (
                <button
                    className={twJoin('underline hover:text-textAccent', showFullDescription && 'mt-2')}
                    onClick={handleButtonClick}
                >
                    {showFullDescription ? 'Close full description' : 'Open full description'}
                    <ArrowSecondaryIcon
                        className={twJoin('ml-2 size-3 text-textDisabled', showFullDescription && 'rotate-180')}
                    />
                </button>
            )}
        </div>
    );
};
