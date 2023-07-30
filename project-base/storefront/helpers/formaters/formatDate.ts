import dayjs, { Dayjs } from 'dayjs';

export const formatDate = (date?: Dayjs | string, format?: string): string => dayjs.utc(date).format(format);

export const formatDateAndTime = (date?: Dayjs | string): string => dayjs(date).format('l LT');
