import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';

type DeliveryOptionsPanelProps = {
    children: ReactNode;
    icon: ReactNode;
    isDesktop: boolean;
    isOpen: boolean;
    panelId: string;
    scrollableContentId?: string;
    summary: string | null;
    title: string;
    onToggle: () => void;
};

export const DeliveryOptionsPanel: FC<DeliveryOptionsPanelProps> = ({
    children,
    icon,
    isDesktop,
    isOpen,
    panelId,
    scrollableContentId,
    summary,
    title,
    onToggle,
}) => {
    const toggleButtonId = `${panelId}-button`;

    return (
        <>
            <button
                aria-controls={panelId}
                aria-expanded={isOpen}
                className="flex w-full cursor-pointer items-center gap-3 px-4 vl:px-5 py-4 text-left lg:hidden"
                id={toggleButtonId}
                type="button"
                onClick={onToggle}
            >
                <span
                    className={twJoin(
                        'flex h-10 w-14 min-w-14 items-center justify-center rounded-xl text-icon-accent',
                        isOpen ? 'bg-background-more' : 'bg-background-default',
                    )}
                >
                    {icon}
                </span>

                <span className="flex min-w-0 flex-1 flex-col">
                    <span className="font-secondary font-semibold text-text-default">{title}</span>

                    {summary !== null && (
                        <span className="font-secondary font-semibold text-sm text-text-less">{summary}</span>
                    )}
                </span>

                <ArrowIcon
                    aria-hidden="true"
                    className={twJoin('size-5 shrink-0 text-icon-accent transition', isOpen && 'rotate-180')}
                />
            </button>

            <h3 className="h5 mb-2 max-lg:hidden">{title}</h3>

            <div
                aria-labelledby={toggleButtonId}
                className={twJoin(
                    'grid transition-[grid-template-rows] duration-200 lg:flex lg:min-h-0 lg:flex-1 lg:flex-col',
                    isOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
                )}
                id={panelId}
            >
                <div
                    aria-hidden={!isDesktop && !isOpen}
                    className="min-h-0 overflow-hidden lg:flex lg:flex-1 lg:flex-col"
                    inert={!isDesktop && !isOpen ? true : undefined}
                >
                    <div
                        className="border-border-less max-lg:border-t max-lg:px-4 max-lg:py-4 lg:min-h-0 lg:flex-1 lg:overflow-y-auto lg:pr-1"
                        id={scrollableContentId}
                    >
                        {children}
                    </div>
                </div>
            </div>
        </>
    );
};
