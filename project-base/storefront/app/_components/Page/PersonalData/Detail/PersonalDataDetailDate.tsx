'use client';

import { Dayjs } from 'dayjs';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type PersonalDataDetailDateProps = {
    date: Dayjs | string | undefined;
};
export const PersonalDataDetailDate: FC<PersonalDataDetailDateProps> = ({ date }) => {
    const { formatDate } = useFormatDate();

    return <>{formatDate(date)}</>;
};
