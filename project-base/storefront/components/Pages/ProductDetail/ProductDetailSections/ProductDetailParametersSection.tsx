import { ColorPreview } from 'components/Basic/ColorPreview/ColorPreview';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
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
        <div key={value.uuid} className="inline-flex items-center gap-2">
            {index > 0 && (
                <>
                    <span className="sr-only">, </span>
                    <span aria-hidden="true" className="text-text-less">
                        ,
                    </span>
                </>
            )}
            {parameter.type === TypeParameterTypeEnum.Color && (
                <ColorPreview colorIcon={value.colorIcon} rgbHex={value.rgbHex} />
            )}
            {value.text}
            {parameter.unit?.name && ` ${parameter.unit.name}`}
        </div>
    ));

type ParameterTableProps = {
    parameters: TypeParameterFragment[];
};

const ParameterTable = ({ parameters }: ParameterTableProps) => (
    <Table
        className="overflow-hidden rounded-xl border border-border-less bg-background-more"
        tableClassName="md:table-fixed"
    >
        {parameters.map((parameter) => (
            <Row
                key={parameter.uuid}
                className="block border-border-less border-b bg-transparent last:border-b-0 odd:bg-transparent md:table-row"
            >
                <Cell
                    isHead
                    className="block px-4 pt-4 pb-1 text-left align-top md:table-cell md:w-[42%] md:border-border-less md:border-r md:px-6 md:py-4"
                    scope="row"
                >
                    <span className="font-normal text-sm">{parameter.name}</span>
                </Cell>

                <Cell className="block bg-background-default px-4 pt-0 pb-4 align-top text-sm md:table-cell md:px-6 md:py-4">
                    <div className="inline-flex flex-wrap gap-x-3 gap-y-1">{renderParameterValues(parameter)}</div>
                </Cell>
            </Row>
        ))}
    </Table>
);

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
            className="scroll-mt-fixed-header-with-navigation"
            data-tid={`${TIDs.product_detail_section_}${PRODUCT_DETAIL_SECTIONS_IDS.parameters}`}
            id={PRODUCT_DETAIL_SECTIONS_IDS.parameters}
            ref={sectionRef}
        >
            <Webline width="vl">
                <ProductDetailSectionHeading>{t('Parameters')}</ProductDetailSectionHeading>

                {groupParameters.map(({ groupName, groupParameters }) => (
                    <Fragment key={groupName}>
                        <h3 className="h4 pt-6 pb-4">{groupName}</h3>

                        <ParameterTable parameters={groupParameters} />
                    </Fragment>
                ))}

                {individualParameters.length > 0 && (
                    <Fragment key="other-parameters">
                        {!!groupParameters.length && <h3 className="h4 pt-6 pb-4">{t('Other parameters')}</h3>}

                        <ParameterTable parameters={individualParameters} />
                    </Fragment>
                )}
            </Webline>
        </div>
    );
};
