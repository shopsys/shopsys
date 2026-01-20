import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { Fragment, RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductDetailParametersSectionProps = {
    parameters: TypeParameterFragment[];
    sectionRef: RefObject<HTMLDivElement>;
};

export const ProductDetailParametersSection = ({ parameters, sectionRef }: ProductDetailParametersSectionProps) => {
    const { t } = useTranslation();

    const formatParameterValue = (valueText: string, index: number) => {
        return index > 0 ? ' | ' + valueText : valueText;
    };

    const sortedIndividualParameters = parameters
        .filter((parameter) => parameter.group === null)
        .sort((a, b) => a.name.localeCompare(b.name));

    const groupedParameters = parameters
        .filter((parameter) => parameter.group !== null)
        .reduce(
            (groupedParametersAccumulator, parameter) => {
                const group = parameter.group as string;
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                groupedParametersAccumulator[group] = groupedParametersAccumulator[group] || [];
                groupedParametersAccumulator[group].push(parameter);
                return groupedParametersAccumulator;
            },
            {} as Record<string, TypeParameterFragment[]>,
        );

    const sortedGroupParameters = Object.entries(groupedParameters).map(([groupName, groupParameters]) => ({
        groupName,
        groupParameters: groupParameters.sort((a, b) => a.name.localeCompare(b.name)),
    }));

    return (
        <div className="scroll-mt-20" id={PRODUCT_DETAIL_SECTIONS_IDS.parameters} ref={sectionRef}>
            <Webline>
                <ProductDetailSectionHeading>{t('Parameters')}</ProductDetailSectionHeading>

                <div className="mx-auto max-w-[700px]">
                    {sortedGroupParameters.map(({ groupName, groupParameters }) => (
                        <Fragment key={groupName}>
                            <p className="h4 py-5">{groupName}</p>

                            <Table>
                                {groupParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="bg-table-bg-default odd:bg-table-bg-contrast border-none"
                                    >
                                        <Cell className="hidden w-60 px-5 py-2.5 align-top lg:table-cell">
                                            <span className="h6 leading-5">{parameter.name}</span>
                                        </Cell>

                                        <Cell className="px-5 py-2.5 text-sm">
                                            <span className="h6 leading-5 lg:hidden">{parameter.name}</span>

                                            {parameter.values.map((value, index) =>
                                                formatParameterValue(
                                                    value.text +
                                                        (parameter.unit?.name ? ` ${parameter.unit.name}` : ''),
                                                    index,
                                                ),
                                            )}
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </Fragment>
                    ))}

                    {sortedIndividualParameters.length > 0 && (
                        <Fragment key="other-parameters">
                            {!!sortedGroupParameters.length && <p className="h4 py-5">{t('Other parameters')}</p>}

                            <Table>
                                {sortedIndividualParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="bg-table-bg-default odd:bg-table-bg-contrast border-none"
                                    >
                                        <Cell className="hidden w-60 px-5 py-2.5 align-top lg:table-cell">
                                            <span className="h6 leading-5">{parameter.name}</span>
                                        </Cell>

                                        <Cell className="px-5 py-2.5 text-sm">
                                            <span className="h6 leading-5 lg:hidden">{parameter.name}</span>

                                            {parameter.values.map((value, index) =>
                                                formatParameterValue(
                                                    value.text +
                                                        (parameter.unit?.name ? ` ${parameter.unit.name}` : ''),
                                                    index,
                                                ),
                                            )}
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </Fragment>
                    )}
                </div>
            </Webline>
        </div>
    );
};
