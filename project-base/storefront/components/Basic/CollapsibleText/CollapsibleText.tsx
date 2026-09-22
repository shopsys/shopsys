import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { m, useReducedMotion } from 'framer-motion';
import { useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type CollapsibleTextProps = {
    text: string;
    textClassName?: string;
};

export const CollapsibleText: FC<CollapsibleTextProps> = ({ text, textClassName }) => {
    const { t } = useTranslation();
    const [showFullDescription, setShowFullDescription] = useState(false);
    const [shouldShowButton, setShouldShowButton] = useState(false);
    const [collapsedHeight, setCollapsedHeight] = useState<number | null>(null);
    const [isScrollingToTop, setIsScrollingToTop] = useState(false);
    const textRef = useRef<HTMLDivElement>(null);
    const hasInteractedRef = useRef(false);
    const shouldReduceMotion = useReducedMotion();

    useEffect(() => {
        const textElement = textRef.current;

        if (!textElement) {
            return undefined;
        }

        const updateHeight = () => {
            const height = Number.parseFloat(getComputedStyle(textElement).lineHeight) * 4;
            setCollapsedHeight(height);
            setShouldShowButton(textElement.scrollHeight > height);
        };

        const resizeObserver = new ResizeObserver(updateHeight);
        updateHeight();
        resizeObserver.observe(textElement);

        return () => resizeObserver.disconnect();
    }, [text]);

    useEffect(() => {
        if (!isScrollingToTop) {
            return undefined;
        }

        const collapseAtTop = () => {
            if (window.scrollY <= 1) {
                setIsScrollingToTop(false);
                setShowFullDescription(false);
            }
        };

        window.addEventListener('scroll', collapseAtTop, { passive: true });
        window.scrollTo({ top: 0, behavior: shouldReduceMotion ? 'instant' : 'smooth' });
        collapseAtTop();

        return () => window.removeEventListener('scroll', collapseAtTop);
    }, [isScrollingToTop, shouldReduceMotion]);

    const handleButtonClick = () => {
        hasInteractedRef.current = true;

        if (showFullDescription) {
            setIsScrollingToTop(true);
        } else {
            setShowFullDescription(true);
        }
    };

    return (
        <div className="flex w-full flex-col items-start gap-2">
            <m.div
                animate={{ height: showFullDescription || !shouldShowButton ? 'auto' : (collapsedHeight ?? 'auto') }}
                initial={false}
                transition={{ type: 'tween', duration: shouldReduceMotion || !hasInteractedRef.current ? 0 : 0.3 }}
                className={twMergeCustom(
                    'relative max-w-2xl overflow-hidden',
                    !showFullDescription &&
                        shouldShowButton &&
                        "after:pointer-events-none after:absolute after:bottom-0 after:left-0 after:h-6 after:w-full after:bg-linear-to-t/srgb after:from-background-default after:to-transparent after:content-['']",
                    textClassName,
                )}
            >
                <div
                    dangerouslySetInnerHTML={{ __html: text }}
                    className={twJoin('user-text', !hasInteractedRef.current && 'max-h-[4lh] overflow-hidden')}
                    ref={textRef}
                />
            </m.div>

            <button
                aria-expanded={showFullDescription}
                type="button"
                className={twJoin(
                    'group flex cursor-pointer items-center gap-2 font-secondary font-semibold text-link-default text-sm no-underline hover:text-link-hovered',
                    !shouldShowButton && 'invisible',
                )}
                onClick={handleButtonClick}
            >
                {showFullDescription ? t('Close full description') : t('Open full description')}
                <ArrowSecondaryIcon
                    className={twJoin(
                        'size-3 transition-transform',
                        showFullDescription ? 'rotate-180 group-hover:-translate-y-0.5' : 'group-hover:translate-y-0.5',
                    )}
                />
            </button>
        </div>
    );
};
