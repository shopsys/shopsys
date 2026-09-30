import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Fragment, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ComparisonParameter } from 'utils/productLists/comparison/getComparisonParameters';

type ProductComparisonBodyProps = {
    parameters: ComparisonParameter[];
    productCount: number;
    onlyDifferences: boolean;
};

export const ProductComparisonBody: FC<ProductComparisonBodyProps> = ({
    parameters,
    productCount,
    onlyDifferences,
}) => {
    const { t } = useTranslation();
    const [collapsedGroups, setCollapsedGroups] = useState<string[]>([]);
    const groups = Array.from(new Set(parameters.map((parameter) => parameter.group)));

    const handleToggleGroup = (groupKey: string) => {
        setCollapsedGroups((previous) =>
            previous.includes(groupKey) ? previous.filter((item) => item !== groupKey) : [...previous, groupKey],
        );
    };

    return (
        <tbody className="block md:table-row-group [&>tr:first-child>th]:border-t">
            {groups.map((group) => {
                const groupKey = group ?? '';
                const rows = parameters.filter(
                    (parameter) => parameter.group === group && (!onlyDifferences || parameter.isDifferent),
                );
                if (!rows.length) {
                    return null;
                }
                const collapsed = collapsedGroups.includes(groupKey);
                return (
                    <Fragment key={groupKey}>
                        <tr className="block md:table-row">
                            <th
                                className="block border-border-less border-b bg-table-bg-contrast p-0 text-left md:table-cell md:border-t"
                                colSpan={productCount + 1}
                            >
                                <button
                                    aria-expanded={!collapsed}
                                    className="flex w-full cursor-pointer items-center justify-between gap-2 p-4 text-left font-semibold text-sm md:sticky md:left-0 md:w-(--comparison-viewport-width)"
                                    type="button"
                                    onClick={() => handleToggleGroup(groupKey)}
                                >
                                    <span>
                                        {group ?? t('Other parameters')}{' '}
                                        <span className="font-normal text-text-less">({rows.length})</span>
                                    </span>
                                    <ArrowIcon
                                        className={twJoin(
                                            'size-5 shrink-0 transition-transform',
                                            !collapsed && 'rotate-180',
                                        )}
                                    />
                                </button>
                            </th>
                        </tr>
                        {!collapsed &&
                            rows.map((parameter) => (
                                <tr
                                    key={parameter.uuid}
                                    className={twJoin(
                                        'group grid grid-cols-2 border-border-less border-b md:table-row',
                                        parameter.isDifferent && productCount > 1
                                            ? 'bg-background-accent/5'
                                            : 'bg-table-bg-default',
                                    )}
                                >
                                    <th
                                        className="col-span-2 px-4 pt-3 text-left font-medium text-text-less text-xs md:sticky md:left-0 md:z-above md:bg-table-bg-default md:py-4 md:text-sm md:text-text-default"
                                        scope="row"
                                        id={`comparison-parameter-${parameter.uuid}`}
                                    >
                                        {parameter.name}
                                        {parameter.unit && (
                                            <span className="ml-1 font-normal text-text-less">({parameter.unit})</span>
                                        )}
                                        {parameter.isDifferent && (
                                            <span className="sr-only"> — {t('Different values')}</span>
                                        )}
                                    </th>

                                    {parameter.values.map((values, index) => (
                                        <td
                                            key={`${parameter.uuid}-${index}`}
                                            headers={`comparison-product-${index} comparison-parameter-${parameter.uuid}`}
                                            className="wrap-break-word min-w-0 border-border-less px-4 pt-1 pb-4 text-sm leading-relaxed transition-colors md:border-l md:py-4 md:group-hover:bg-table-bg-contrast"
                                        >
                                            {values ? (
                                                values.join(', ')
                                            ) : (
                                                <>
                                                    <span aria-hidden="true" className="text-text-less">
                                                        —
                                                    </span>
                                                    <span className="sr-only">{t('Not specified')}</span>
                                                </>
                                            )}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                    </Fragment>
                );
            })}
            {!parameters.some((parameter) => !onlyDifferences || parameter.isDifferent) && (
                <tr className="block md:table-row">
                    <td className="block p-8 text-center text-text-less md:table-cell" colSpan={productCount + 1}>
                        {onlyDifferences && parameters.length > 0
                            ? t(
                                  'The selected products have the same parameters. Turn off the filter to see all values.',
                              )
                            : t('No parameters are available for these products.')}
                    </td>
                </tr>
            )}
        </tbody>
    );
};
