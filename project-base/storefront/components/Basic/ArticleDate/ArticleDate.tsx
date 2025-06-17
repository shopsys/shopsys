import dayjs from 'dayjs';
import { twJoin } from 'tailwind-merge';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type ArticleDate = {
    date: string;
    tid?: string;
    className?: string;
};

export const ArticleDate: FC<ArticleDate> = ({ date, tid, className }) => {
    const { formatDate } = useFormatDate();

    const isoDateTime = dayjs.utc(date).format('YYYY-MM-DD'); //required by HTML spec
    const displayDate = formatDate(date);

    return (
        <time
            className={twJoin('font-secondary text-text-less text-sm font-semibold', className)}
            data-tid={tid}
            dateTime={isoDateTime}
        >
            {displayDate}
        </time>
    );
};
