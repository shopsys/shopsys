import { Webline } from 'components/Layout/Webline/Webline';
import { StyleguideButtons } from './StyleguideButtons';
import { StyleguideCheckboxes } from './StyleguideCheckboxes';
import { StyleguideColors } from './StyleguideColors';
import { StyleguideForms } from './StyleguideForms';
import { StyleguideIcons } from './StyleguideIcons.generated';
import { StyleguideInfobox } from './StyleguideInfobox';
import { StyleguideNotImplementedYet } from './StyleguideNotImplementedYet';
import { StyleguidePopups } from './StyleguidePopups';
import { StyleguideRadiogroup } from './StyleguideRadiogroup';
import { StyleguideSelects } from './StyleguideSelects';
import { StyleguideSpinboxes } from './StyleguideSpinboxes';
import { StyleguideTables } from './StyleguideTables';
import { StyleguideToasts } from './StyleguideToasts';
import { StyleguideTooltips } from './StyleguideTooltips';
import { StyleguideTypography } from './StyleguideTypography';

type StyleguideContentProps = { tailwindColors?: Record<string, any> };

export const StyleguideContent: FC<StyleguideContentProps> = ({ tailwindColors }) => {
    return (
        <Webline className="mb-10 flex flex-col gap-10">
            {tailwindColors && <StyleguideColors tailwindColors={tailwindColors} />}
            <StyleguideTypography />
            <StyleguideButtons />
            <StyleguidePopups />
            <StyleguideToasts />
            <StyleguideTooltips />
            <StyleguideInfobox />
            <StyleguideForms />
            <StyleguideSelects />
            <StyleguideRadiogroup />
            <StyleguideCheckboxes />
            <StyleguideSpinboxes />
            <StyleguideTables />
            <StyleguideIcons />
            <StyleguideNotImplementedYet />
        </Webline>
    );
};
