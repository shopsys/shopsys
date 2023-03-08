export const ProductVariantsTableRow: FC = ({ children, dataTestId }) => (
    <tr
        className="max-lg:relative max-lg:ml-1 max-lg:mb-1 max-lg:block max-lg:w-[calc(50%-4px)] max-lg:border max-lg:border-greyLighter max-lg:p-1 max-md:ml-0 max-md:w-full"
        data-testid={dataTestId}
    >
        {children}
    </tr>
);
