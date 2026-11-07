import { KeyboardEvent, ReactElement, useEffect, useId, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

export type FilterPanelGroup = {
    id: string;
    title: string;
    isActive: boolean;
    isAvailable: boolean;
    content: ReactElement;
};

export const FilterPanelDesktopContent: FC<{ groups: FilterPanelGroup[] }> = ({ groups }) => {
    const { t } = useTranslation();
    const id = useId();
    const hadPanelFocus = useRef(false);
    const firstAvailableGroup = groups.find((group) => group.isAvailable || group.isActive) ?? groups[0];
    const [selectedGroupId, setSelectedGroupId] = useState(
        () => groups.find((group) => group.isActive)?.id ?? firstAvailableGroup?.id,
    );
    const selectedGroup = groups.find((group) => group.id === selectedGroupId) ?? firstAvailableGroup;
    const isSelectedGroupAvailable = !!selectedGroup && (selectedGroup.isAvailable || selectedGroup.isActive);

    useEffect(() => {
        if (selectedGroup && selectedGroup.id !== selectedGroupId) {
            setSelectedGroupId(selectedGroup.id);
        }
        // A live update can disable a focused choice while its group remains available.
        if (
            selectedGroup &&
            hadPanelFocus.current &&
            (document.activeElement === document.body || document.activeElement?.matches(':disabled'))
        ) {
            document.getElementById(`${id}-tab-${selectedGroup.id}`)?.focus();
            hadPanelFocus.current = false;
        }
    });

    const handleGroupKeyDown = (event: KeyboardEvent<HTMLButtonElement>, index: number) => {
        let nextGroup: FilterPanelGroup | undefined;
        const availableGroups = groups.filter((group) => group.isAvailable || group.isActive);
        switch (event.key) {
            case 'ArrowDown':
            case 'ArrowUp': {
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                for (let offset = 1; offset <= groups.length; offset++) {
                    const candidate = groups[(index + direction * offset + groups.length) % groups.length];
                    if (candidate.isAvailable || candidate.isActive) {
                        nextGroup = candidate;
                        break;
                    }
                }
                break;
            }
            case 'Home':
                nextGroup = availableGroups[0];
                break;
            case 'End':
                nextGroup = availableGroups.at(-1);
                break;
            default:
                return;
        }
        event.preventDefault();
        if (nextGroup) {
            setSelectedGroupId(nextGroup.id);
            document.getElementById(`${id}-tab-${nextGroup.id}`)?.focus();
        }
    };

    return (
        <div className="grid min-h-0 flex-1 grid-cols-[13rem_minmax(0,1fr)] border-border-less border-t">
            <div
                aria-label={t('Product filters', { ns: 'accessibility' })}
                aria-orientation="vertical"
                className="thin-scrollbar flex min-h-0 flex-col gap-1 overflow-y-auto border-border-less border-r py-3 pr-3"
                role="tablist"
            >
                {groups.map((group, index) => {
                    const isAvailable = group.isAvailable || group.isActive;
                    const isSelected = group.id === selectedGroup?.id;
                    return (
                        <button
                            key={group.id}
                            aria-controls={`${id}-panel`}
                            aria-disabled={!isAvailable}
                            disabled={!isAvailable && !isSelected}
                            aria-selected={isSelected}
                            id={`${id}-tab-${group.id}`}
                            role="tab"
                            tabIndex={isSelected ? 0 : -1}
                            type="button"
                            className={twMergeCustom(
                                'flex min-h-10 shrink-0 items-center justify-between gap-3 rounded-md px-3 py-2.5 text-left font-secondary font-semibold text-sm transition-colors',
                                isAvailable
                                    ? 'hover:bg-background-more'
                                    : 'cursor-not-allowed text-input-text-disabled opacity-50',
                                isSelected && 'bg-background-accent-less',
                                isSelected && isAvailable && 'text-link-default hover:bg-background-accent-less',
                            )}
                            onClick={() => isAvailable && setSelectedGroupId(group.id)}
                            onKeyDown={(event) => handleGroupKeyDown(event, index)}
                        >
                            <span className="min-w-0 break-words">{group.title}</span>
                            {group.isActive && (
                                <span
                                    aria-label={t('Active filters')}
                                    className="size-2 shrink-0 rounded-full bg-link-default"
                                />
                            )}
                        </button>
                    );
                })}
            </div>
            {selectedGroup && (
                <div
                    aria-labelledby={`${id}-tab-${selectedGroup.id}`}
                    aria-describedby={isSelectedGroupAvailable ? undefined : `${id}-unavailable`}
                    className="thin-scrollbar min-h-0 overflow-y-auto px-6 py-5"
                    id={`${id}-panel`}
                    role="tabpanel"
                    onFocusCapture={() => {
                        hadPanelFocus.current = true;
                    }}
                    onBlurCapture={(event) => {
                        if (event.relatedTarget instanceof Node && event.relatedTarget !== document.body) {
                            hadPanelFocus.current = event.currentTarget.contains(event.relatedTarget);
                        }
                    }}
                >
                    <h3 className="mb-6 font-secondary font-semibold text-xl">{selectedGroup.title}</h3>
                    {isSelectedGroupAvailable ? (
                        <div key={selectedGroup.id}>{selectedGroup.content}</div>
                    ) : (
                        <p className="text-text-less" id={`${id}-unavailable`} role="status">
                            {t('No options are available for the current selection.')}
                        </p>
                    )}
                </div>
            )}
        </div>
    );
};
