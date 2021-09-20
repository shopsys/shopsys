import { FC, useState } from 'react';
import { PaginationButtonStyled, PaginationWrapperStyled } from './Pagination.style';

export type PaginationButtonType = number;

export type PaginationButtonActiveType = {
    active: boolean;
};

const Pagination: FC = () => {
    const [selectedPage, setSelectedPage] = useState(1);

    return (
        <PaginationWrapperStyled>
            <PaginationButtonStyled
                active={selectedPage === 1}
                onClick={() => {
                    setSelectedPage(1);
                }}
            >
                1
            </PaginationButtonStyled>
            <PaginationButtonStyled
                active={selectedPage === 2}
                onClick={() => {
                    setSelectedPage(2);
                }}
            >
                2
            </PaginationButtonStyled>
            <PaginationButtonStyled
                active={selectedPage === 3}
                onClick={() => {
                    setSelectedPage(3);
                }}
            >
                3
            </PaginationButtonStyled>
            <PaginationButtonStyled
                active={selectedPage === 4}
                onClick={() => {
                    setSelectedPage(4);
                }}
            >
                4
            </PaginationButtonStyled>
            <PaginationButtonStyled active={selectedPage === 0}>...</PaginationButtonStyled>
            {/* TODO PRG: connect to actual products */}
            <PaginationButtonStyled active={selectedPage === 24}>24</PaginationButtonStyled>
        </PaginationWrapperStyled>
    );
};

export default Pagination;
