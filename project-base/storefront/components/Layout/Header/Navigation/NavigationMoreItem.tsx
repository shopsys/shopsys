import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import type { KeyboardEventHandler, Ref } from 'react';
import { useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type NavigationMoreItemProps = {
    isOpened: boolean;
    itemRef?: Ref<HTMLLIElement>;
    itemClassName?: string;
    menuId: string;
    onOpen: (shouldDelay?: boolean) => void;
    onClose: () => void;
    onToggle: () => void;
};

export const NavigationMoreItem: FC<NavigationMoreItemProps> = ({
    isOpened,
    itemRef,
    itemClassName,
    menuId,
    onOpen,
    onClose,
    onToggle,
}) => {
    const { t } = useTranslation();
    const isPointerInteractionRef = useRef(false);

    const handleKeyDown: KeyboardEventHandler<HTMLLIElement> = (event) => {
        if (event.key === 'Escape') {
            onClose();
        }
    };

    return (
        // biome-ignore lint/a11y/noNoninteractiveElementInteractions: The list item owns the hover/focus state for its dropdown links.
        <li
            className="group relative"
            ref={itemRef}
            onFocus={() => {
                if (!isPointerInteractionRef.current) {
                    onOpen(false);
                }
            }}
            onKeyDown={handleKeyDown}
            onMouseEnter={() => onOpen(true)}
            onPointerCancel={() => {
                isPointerInteractionRef.current = false;
            }}
            onPointerDown={() => {
                isPointerInteractionRef.current = true;
            }}
            onPointerLeave={() => {
                isPointerInteractionRef.current = false;
            }}
            onPointerUp={() => {
                isPointerInteractionRef.current = false;
            }}
        >
            <button
                aria-controls={menuId}
                aria-expanded={isOpened}
                className={twMergeCustom(
                    'relative m-0 flex cursor-pointer items-center whitespace-nowrap rounded-sm border-0 bg-transparent p-5 font-secondary font-semibold text-sm vl:text-base group-first-of-type:pl-0 group-last-of-type:pr-0',
                    'text-link-inverted-default',
                    'hover:text-link-inverted-hovered group-hover:text-link-inverted-hovered',
                    'active:text-link-inverted-hovered',
                    itemClassName,
                )}
                type="button"
                onClick={onToggle}
            >
                {t('More')}
                <span
                    className={twJoin(
                        'ml-1 flex items-start motion-safe:transition-transform motion-safe:duration-200',
                        isOpened && 'rotate-180',
                    )}
                >
                    <ArrowIcon
                        className={twJoin(
                            'size-5 text-link-inverted-default transition',
                            isOpened && 'group-hover:text-link-inverted-hovered',
                        )}
                    />
                </span>
            </button>
        </li>
    );
};
