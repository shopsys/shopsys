import { Heading, HeadingProps } from './Heading';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';

type HeadingPaginatedProps = HeadingProps & {
    totalCount: number;
};

export const HeadingPaginated: FC<HeadingPaginatedProps> = ({ totalCount, children, ...headingProps }) => {
    const t = useTypedTranslationFunction();
    const { currentPage } = useQueryParams();
    const totalPages = Math.ceil(totalCount / DEFAULT_PAGE_SIZE);
    const additionalPaginationText =
        ' ' +
        t('page {{ currentPage }} from {{ totalPages }}', {
            totalPages: totalPages,
            currentPage: currentPage,
        });

    return (
        <Heading {...headingProps}>
            {children}
            {totalPages > 1 ? additionalPaginationText : ''}
        </Heading>
    );
};
