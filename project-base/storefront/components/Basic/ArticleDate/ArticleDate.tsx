import { twJoin } from 'tailwind-merge';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type ArticleDate = {
    date: string;
    tid?: string;
    className?: string;
};

const toIsoDateUTC = (dateString: string): string => {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
        return '';
    }
    return date.toISOString().split('T')[0];
};

export const ArticleDate: FC<ArticleDate> = ({ date, tid, className }) => {
    const { formatDate } = useFormatDate();

    const isoDateTime = toIsoDateUTC(date); // Required by HTML spec
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
