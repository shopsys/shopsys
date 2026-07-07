import * as Popover from '@radix-ui/react-popover';
import { DatePickerChevron } from 'components/Forms/DatePicker/DatePickerChevron';
import {
    datePickerClassNames,
    formatDatePickerValue,
    formatDisplayDate,
    getDatePickerFormatters,
    getDatePickerLabels,
    parseDatePickerValue,
} from 'components/Forms/DatePicker/datePickerUtils';
import { useState } from 'react';
import { DayPicker } from 'react-day-picker';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type DatePickerProps = {
    id: string;
    label: string;
    name: string;
    value: string;
    onChange: (value: string) => void;
    className?: string;
};

export const DatePicker: FC<DatePickerProps> = ({ id, label, name, value, onChange, className }) => {
    const { lang, t } = useTranslation();
    const [isOpen, setIsOpen] = useState(false);
    const selectedDate = parseDatePickerValue(value);
    const datePickerFormatters = getDatePickerFormatters(t);
    const datePickerLabels = getDatePickerLabels(t, lang);

    const handleSelect = (date: Date | undefined) => {
        if (!date) {
            return;
        }

        onChange(formatDatePickerValue(date));
        setIsOpen(false);
    };

    return (
        <Popover.Root open={isOpen} onOpenChange={setIsOpen}>
            <div className={twMergeCustom('relative w-full font-secondary', className)}>
                <input name={name} type="hidden" value={value} />

                <Popover.Trigger asChild>
                    <button
                        aria-label={label}
                        className={twMergeCustom(
                            'group flex h-14 w-full cursor-pointer items-center rounded-input border-2 border-input-border-default bg-input-bg-default px-3 pt-5 text-left font-semibold text-input-text-default transition hover:border-input-border-hovered hover:text-input-text-hovered focus:border-input-border-active focus:text-input-text-active focus:outline-hidden',
                            isOpen && 'border-input-border-active text-input-text-active',
                        )}
                        id={id}
                        name={name}
                        type="button"
                    >
                        <span
                            className={twJoin(
                                'pointer-events-none absolute left-3 block max-w-[calc(100%-3rem)] truncate text-input-placeholder-default transition-all group-hover:text-input-placeholder-hovered group-focus:text-input-placeholder-active',
                                isOpen || value !== ''
                                    ? 'top-2 text-sm'
                                    : 'top-1/2 -translate-y-1/2 font-semibold text-base',
                            )}
                        >
                            {label}
                        </span>

                        <span className="block min-w-0 flex-1 truncate">
                            {selectedDate ? formatDisplayDate(selectedDate, lang) : ''}
                        </span>
                    </button>
                </Popover.Trigger>
            </div>

            <Popover.Portal>
                <Popover.Content
                    align="start"
                    className="z-maximum mt-1 rounded-input border border-input-border-default bg-background-default p-3 shadow-lg"
                    sideOffset={4}
                >
                    <DayPicker
                        showOutsideDays
                        classNames={datePickerClassNames}
                        components={{ Chevron: DatePickerChevron }}
                        defaultMonth={selectedDate}
                        formatters={datePickerFormatters}
                        labels={datePickerLabels}
                        mode="single"
                        selected={selectedDate}
                        weekStartsOn={1}
                        onSelect={handleSelect}
                    />

                    <div className="flex items-center justify-between gap-4">
                        {value !== '' && (
                            <button
                                className="cursor-pointer rounded-button px-2 py-1 font-secondary font-semibold text-link-default text-sm underline hover:text-link-hovered"
                                type="button"
                                onClick={() => {
                                    onChange('');
                                    setIsOpen(false);
                                }}
                            >
                                {t('Clear')}
                            </button>
                        )}

                        <button
                            className="ml-auto cursor-pointer rounded-button px-2 py-1 font-secondary font-semibold text-link-default text-sm underline hover:text-link-hovered"
                            type="button"
                            onClick={() => {
                                onChange(formatDatePickerValue(new Date()));
                                setIsOpen(false);
                            }}
                        >
                            {t('Today')}
                        </button>
                    </div>
                </Popover.Content>
            </Popover.Portal>
        </Popover.Root>
    );
};
