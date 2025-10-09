import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';
import PasswordStrengthCalculator from './utils/passwordStrengthCalculator';

export default class PasswordWithStrengthInput {
    constructor($input) {
        const requirementRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}$/;

        const progressBarHtml = `<div id="password-strength-meter" class="mt-2">
            <div class="progress" style="height: 0.5em;">
                <div class="progress-bar" role="progressbar" style="width: 0%;"></div>
            </div>
            <div class="text-end text-nowrap small strength-text" style="min-height: 1em"><span></span></div>
        </div>`;
        $input.after(progressBarHtml);

        $input.on('keyup', event => {
            const $input = $(event.currentTarget);
            const value = $input.val();

            const passwordMeetRequirements = requirementRegex.test(value);

            if (passwordMeetRequirements) {
                $input.removeClass('is-invalid');
            } else {
                $input.addClass('is-invalid');
            }

            const strength = PasswordStrengthCalculator.calculate(value);
            this.updateProgressBar(passwordMeetRequirements ? strength : null);
            this.visualizeHelpText(value);
        });
    }

    updateProgressBar(strength) {
        const $progressBar = $('#password-strength-meter .progress-bar');
        const $text = $('#password-strength-meter .strength-text span');

        let width;
        let status;
        let text;

        switch (strength) {
            case PasswordStrengthCalculator.STRENGTH_VERY_WEAK:
                width = '20%';
                status = 'red';
                text = Translator.trans('Very Weak');
                break;
            case PasswordStrengthCalculator.STRENGTH_WEAK:
                width = '40%';
                status = 'orange';
                text = Translator.trans('Weak');
                break;
            case PasswordStrengthCalculator.STRENGTH_MEDIUM:
                width = '60%';
                status = 'yellow';
                text = Translator.trans('Medium');
                break;
            case PasswordStrengthCalculator.STRENGTH_STRONG:
                width = '80%';
                status = 'azure';
                text = Translator.trans('Strong');
                break;
            case PasswordStrengthCalculator.STRENGTH_VERY_STRONG:
                width = '100%';
                status = 'green';
                text = Translator.trans('Awesome');
                break;
            default:
                width = '0%';
                status = 'red';
                text = '';
        }

        $progressBar.removeClass();
        $text.removeClass();

        $progressBar.addClass(`progress-bar bg-${status}`);

        $text.addClass(`text-${status}`);
        $text.text(text);

        $progressBar.css('width', width);
    }

    visualizeHelpText(value) {
        const $helpText = $('[data-js-password-help-text]');

        $helpText.filterAllNodes('[data-js-password-help-text-requirement]').removeClass();

        const classes = {};

        classes.lower = /[a-z]/.test(value) ? 'text-correct' : 'text-incorrect';
        classes.upper = /[A-Z]/.test(value) ? 'text-correct' : 'text-incorrect';
        classes.number = /\d/.test(value) ? 'text-correct' : 'text-incorrect';
        classes.length = value.length >= 10 ? 'text-correct' : 'text-incorrect';

        for (const key in classes) {
            $helpText.filterAllNodes(`[data-js-password-help-text-requirement="${key}"]`).addClass(classes[key]);
        }
    }

    static init($container) {
        $container.filterAllNodes('[data-js-set-password-input]').each(function () {
            // eslint-disable-next-line no-new
            new PasswordWithStrengthInput($(this));
        });
    }
}

new Register().registerCallback(PasswordWithStrengthInput.init, 'PasswordWithStrengthInput.init');
