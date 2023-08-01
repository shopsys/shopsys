import 'dayjs/locale/cs';
import 'dayjs/locale/sk';
import { Dayjs, extend, locale } from 'dayjs';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import utcPlugin from 'dayjs/plugin/utc';
import timezonePlugin from 'dayjs/plugin/timezone';
import dayjs from 'dayjs';

dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
extend(LocalizedFormat);

export const initDayjsLocale = (defaultLocale: string) => locale(defaultLocale);

export const formatDate = (date?: Dayjs | string, timezone?: string, format?: string): string =>
    dayjs(date).tz(timezone).format(format);

export const formatDateAndTime = (date?: Dayjs | string, timezone?: string): string =>
    dayjs(date).tz(timezone).format('l LT');
