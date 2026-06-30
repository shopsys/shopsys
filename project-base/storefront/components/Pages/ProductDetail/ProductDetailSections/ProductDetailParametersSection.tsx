import { ColorPreview } from 'components/Basic/ColorPreview/ColorPreview';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeParameterTypeEnum } from 'graphql/types';
import { Fragment, RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

type ProductDetailParametersSectionProps = {
    parameters: TypeParameterFragment[];
    sectionRef: RefObject<HTMLDivElement | null>;
};

const renderParameterValues = (parameter: TypeParameterFragment) =>
    parameter.values.map((value, index) => (
        <div key={value.uuid} className="inline-flex items-center gap-1">
            {index > 0 && <span>|</span>}
            {parameter.type === TypeParameterTypeEnum.Color && (
                <ColorPreview colorIcon={value.colorIcon} rgbHex={value.rgbHex} />
            )}
            {value.text}
            {parameter.unit?.name && ` ${parameter.unit.name}`}
        </div>
    ));

export const ProductDetailParametersSection = ({ parameters, sectionRef }: ProductDetailParametersSectionProps) => {
    const { t } = useTranslation();

    const individualParameters = parameters.filter((parameter) => parameter.group === null);

    const groupedParameters = parameters
        .filter((parameter) => parameter.group !== null)
        .reduce(
            (groupedParametersAccumulator, parameter) => {
                const group = parameter.group as string;
                groupedParametersAccumulator[group] = groupedParametersAccumulator[group] || [];
                groupedParametersAccumulator[group].push(parameter);
                return groupedParametersAccumulator;
            },
            {} as Record<string, TypeParameterFragment[]>,
        );

    const groupParameters = Object.entries(groupedParameters).map(([groupName, groupParams]) => ({
        groupName,
        groupParameters: groupParams,
    }));

    return (
        <div
            className="scroll-mt-[calc(var(--sticky-navigation-offset,0)+5rem)]"
            id={PRODUCT_DETAIL_SECTIONS_IDS.parameters}
            ref={sectionRef}
        >
            <Webline>
                <ProductDetailSectionHeading>{t('Parameters')}</ProductDetailSectionHeading>

                <div className="mx-auto max-w-[700px]">
                    {groupParameters.map(({ groupName, groupParameters }) => (
                        <Fragment key={groupName}>
                            <p className="h4 py-5">{groupName}</p>

                            <Table>
                                {groupParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="border-none bg-table-bg-default odd:bg-table-bg-contrast"
                                    >
                                        <Cell className="hidden w-60 px-5 py-2.5 align-top lg:table-cell">
                                            <span className="h6 leading-5">{parameter.name}</span>
                                        </Cell>

                                        <Cell className="flex flex-col gap-1 px-5 py-2.5 text-sm lg:flex-row">
                                            <span className="h6 leading-5 lg:hidden">{parameter.name}</span>

                                            <div className="inline-flex flex-wrap gap-1">
                                                {renderParameterValues(parameter)}
                                            </div>
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </Fragment>
                    ))}

                    {individualParameters.length > 0 && (
                        <Fragment key="other-parameters">
                            {!!groupParameters.length && <p className="h4 py-5">{t('Other parameters')}</p>}

                            <Table>
                                {individualParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="border-none bg-table-bg-default odd:bg-table-bg-contrast"
                                    >
                                        <Cell className="hidden w-60 px-5 py-2.5 align-top lg:table-cell">
                                            <span className="h6 leading-5">{parameter.name}</span>
                                        </Cell>

                                        <Cell className="flex flex-col gap-1 px-5 py-2.5 text-sm lg:flex-row">
                                            <span className="h6 leading-5 lg:hidden">{parameter.name}</span>

                                            <div className="inline-flex flex-wrap gap-1">
                                                {renderParameterValues(parameter)}
                                            </div>
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
