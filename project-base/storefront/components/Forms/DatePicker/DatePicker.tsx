import * as Popover from '@radix-ui/react-popover';
import { TextButton } from 'components/Basic/TextButton/TextButton';
import { DatePickerChevron } from 'components/Forms/DatePicker/DatePickerChevron';
import {
    datePickerClassNames,
    formatDatePickerValue,
    formatDisplayDate,
    getDatePickerFormatters,
    getDatePickerLabels,
    parseDatePickerValue,
} from 'components/Forms/DatePicker/datePickerUtils';
import {
    FIELD_FLOATING_LABEL_CLASS_NAME,
    FIELD_OPEN_CLASS_NAME,
    FIELD_VALUE_PADDING_CLASS_NAME,
    getFieldHeightClassName,
    getFieldStateClassName,
} from 'components/Forms/Lib/fieldUtils';
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

    const clearDate = () => {
        onChange('');
        setIsOpen(false);
    };

    const selectToday = () => {
        onChange(formatDatePickerValue(new Date()));
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
                            'group flex w-full cursor-pointer items-center rounded-input px-3 text-left font-medium focus:outline-hidden',
                            FIELD_VALUE_PADDING_CLASS_NAME,
                            getFieldStateClassName(),
                            getFieldHeightClassName(),
                            isOpen && FIELD_OPEN_CLASS_NAME,
                        )}
                        id={id}
                        name={name}
                        type="button"
                    >
                        <span
                            className={twJoin(
                                'pointer-events-none absolute left-3 block max-w-[calc(100%-3rem)] truncate text-input-placeholder-default transition-all group-hover:text-input-placeholder-hovered group-focus:text-input-placeholder-active',
                                isOpen || value !== ''
                                    ? FIELD_FLOATING_LABEL_CLASS_NAME
                                    : 'top-1/2 -translate-y-1/2 text-md',
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
                            <TextButton className="rounded-button px-2 py-1" onClick={clearDate}>
                                {t('Clear')}
                            </TextButton>
                        )}

                        <TextButton className="ml-auto rounded-button px-2 py-1" onClick={selectToday}>
                            {t('Today')}
                        </TextButton>
                    </div>
                </Popover.Content>
            </Popover.Portal>
        </Popover.Root>
    );
};
