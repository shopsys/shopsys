import { StyleguideSection, StyleguideSubSection } from './StyleguideElements';
import { Cell, CellHead, CellMinor, Row, Table } from 'components/Basic/Table/Table';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const StyleguideTables: FC = () => {
    return (
        <StyleguideSection className="flex flex-col gap-5" title="Tables">
            <StyleguideSubSection title="Regular table">
                <Table
                    className="w-full"
                    head={
                        <Row>
                            <CellHead>Regular head cell</CellHead>
                            <CellHead align="center">Align center</CellHead>
                            <CellHead align="right">Align right</CellHead>
                            <CellHead isWithoutWrap>Without wrap</CellHead>
                            <CellHead className="min-w-[150px]">min-w-[150px]</CellHead>
                            <CellHead>Regular head cell</CellHead>
                            <CellHead>Regular head cell</CellHead>
                            <CellHead>Regular head cell</CellHead>
                        </Row>
                    }
                >
                    {createEmptyArray(3).map((_, index) => (
                        <Row key={index}>
                            <Cell>Regular cell</Cell>
                            <Cell align="center">Align center</Cell>
                            <Cell align="right">Align right</Cell>
                            <Cell isWithoutWrap>Without wrap</Cell>
                            <Cell>min-w-[150px]</Cell>
                            <Cell>Regular cell</Cell>
                            <Cell>Regular cell</Cell>
                            <Cell>Regular cell</Cell>
                        </Row>
                    ))}
                </Table>
            </StyleguideSubSection>

            <StyleguideSubSection title="Simple table">
                <Table className="max-w-96">
                    <Row className="flex flex-col md:flex-row">
                        <Cell className="flex-1">
                            <div className="border-b-2 border-borderAccent p-4 pl-0 text-lg text-tableText">
                                Title of the section
                            </div>

                            <Table className="border-0 p-0">
                                <Row>
                                    <CellMinor>General value</CellMinor>
                                    <Cell>General value</Cell>
                                </Row>

                                <Row>
                                    <CellMinor>General value</CellMinor>
                                    <Cell>General value</Cell>
                                </Row>
                            </Table>
                        </Cell>
                    </Row>
                </Table>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
