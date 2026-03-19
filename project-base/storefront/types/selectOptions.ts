export type SelectOptionType<T = string> = {
    value: T;
    label: string;
    filterValue?: string;
    isDisabled?: boolean;
    count?: number | string;
};
