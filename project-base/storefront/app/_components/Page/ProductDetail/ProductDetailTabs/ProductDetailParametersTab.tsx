import { Cell, Row, Table } from 'app/_components/Basic/Table/Table';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.ssr';
import { Fragment } from 'react';

export type ProductDetailParametersTabProps = {
    parameters: TypeParameterFragment[];
};

export const ProductDetailParametersTab: FC<ProductDetailParametersTabProps> = async ({ parameters }) => {
    const t = await getTranslation();

    if (!parameters.length) {
        return null;
    }

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
        <div className="mx-auto max-w-[700px]">
            {sortedGroupParameters.map(({ groupName, groupParameters }) => (
                <Fragment key={groupName}>
                    <h4 className="py-5">{groupName}</h4>
                    <Table>
                        {groupParameters.map((parameter) => (
                            <Row
                                key={parameter.uuid}
                                className="bg-tableBackground odd:bg-tableBackgroundContrast border-none"
                            >
                                <Cell className="hidden w-[240px] px-5 py-2.5 align-top lg:table-cell">
                                    <h6 className="leading-5">{parameter.name}</h6>
                                </Cell>
                                <Cell className="px-5 py-2.5 text-sm">
                                    <h6 className="leading-5 lg:hidden">{parameter.name}</h6>
                                    {parameter.values.map((value, index) =>
                                        formatParameterValue(
                                            value.text + (parameter.unit?.name ? ` (${parameter.unit.name})` : ''),
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
                    {!!sortedGroupParameters.length && <h4 className="py-5">{t('Other parameters')}</h4>}

                    <Table>
                        {sortedIndividualParameters.map((parameter) => (
                            <Fragment key={parameter.uuid}>
                                <Row
                                    key={parameter.uuid}
                                    className="bg-tableBackground odd:bg-tableBackgroundContrast border-none"
                                >
                                    <Cell className="hidden w-[240px] px-5 py-2.5 align-top lg:table-cell">
                                        <h6 className="leading-5">{parameter.name}</h6>
                                    </Cell>
                                    <Cell className="px-5 py-2.5 text-sm">
                                        <h6 className="leading-5 lg:hidden">{parameter.name}</h6>
                                        {parameter.values.map((value, index) =>
                                            formatParameterValue(
                                                value.text + (parameter.unit?.name ? ` (${parameter.unit.name})` : ''),
                                                index,
                                            ),
                                        )}
                                    </Cell>
                                </Row>
                            </Fragment>
                        ))}
                    </Table>
                </Fragment>
            )}
        </div>
    );
};
