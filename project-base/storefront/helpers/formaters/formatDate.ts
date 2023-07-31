import 'dayjs/locale/cs';
import 'dayjs/locale/sk';
import { Dayjs, extend, locale } from 'dayjs';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import utc from 'dayjs/plugin/utc';
import dayjs from 'dayjs';

dayjs.extend(utc);
extend(LocalizedFormat);

export const initDayjsLocale = (defaultLocale: string) => locale(defaultLocale);

export const formatDate = (date?: Dayjs | string, format?: string): string => dayjs.utc(date).format(format);

export const formatDateAndTime = (date?: Dayjs | string): string => dayjs(date).format('l LT');
