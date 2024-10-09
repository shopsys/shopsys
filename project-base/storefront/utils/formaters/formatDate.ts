import { Dayjs, extend, locale } from 'dayjs';
import dayjs from 'dayjs';
import 'dayjs/locale/cs';
import 'dayjs/locale/sk';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import timezonePlugin from 'dayjs/plugin/timezone';
import utcPlugin from 'dayjs/plugin/utc';

dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
extend(LocalizedFormat);

export const initDayjsLocale = (defaultLocale: string) => locale(defaultLocale);

export const formatDate = (date?: Dayjs | string, timezone?: string): string => dayjs(date).tz(timezone).format('l');

export const formatDateAndTime = (date?: Dayjs | string, timezone?: string): string =>
    dayjs(date).tz(timezone).format('l LT');
