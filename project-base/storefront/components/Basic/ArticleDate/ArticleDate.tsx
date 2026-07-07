import { twJoin } from 'tailwind-merge';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type ArticleDate = {
    date?: string | null;
    tid?: string;
    className?: string;
};

const toIsoDateUTC = (dateString: string): string => {
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    return date.toISOString().split('T')[0];
};

export const ArticleDate: FC<ArticleDate> = ({ date, tid, className }) => {
    const { formatDate } = useFormatDate();

    if (!date) {
        return null;
    }

    const isoDateTime = toIsoDateUTC(date); // Required by HTML spec
    const displayDate = formatDate(date);

    return (
        <time
            className={twJoin('font-secondary font-semibold text-sm text-text-less', className)}
            data-tid={tid}
            dateTime={isoDateTime}
        >
            {displayDate}
        </time>
    );
};
