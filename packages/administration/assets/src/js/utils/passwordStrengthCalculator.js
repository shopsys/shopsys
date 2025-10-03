export default class PasswordStrengthCalculator {
    static STRENGTH_VERY_WEAK = 'VERY_WEAK';
    static STRENGTH_WEAK = 'WEAK';
    static STRENGTH_MEDIUM = 'MEDIUM';
    static STRENGTH_STRONG = 'STRONG';
    static STRENGTH_VERY_STRONG = 'VERY_STRONG';

    // Entropy calculation based on the Symfony Password Strength Estimator
    static calculate(password) {
        const length = password.length;

        if (!length) {
            return this.STRENGTH_VERY_WEAK;
        }

        const charCounts = {};
        for (const char of password) {
            const code = char.charCodeAt(0);
            charCounts[code] = (charCounts[code] || 0) + 1;
        }

        const chars = Object.keys(charCounts).length;

        let control = 0,
            digit = 0,
            upper = 0,
            lower = 0,
            symbol = 0,
            other = 0;

        for (const chrStr in charCounts) {
            const chr = parseInt(chrStr, 10);

            if (chr < 32 || chr === 127) {
                control = 33;
            } else if (48 <= chr && chr <= 57) {
                digit = 10;
            } else if (65 <= chr && chr <= 90) {
                upper = 26;
            } else if (97 <= chr && chr <= 122) {
                lower = 26;
            } else if (128 <= chr) {
                other = 128;
            } else {
                symbol = 33;
            }
        }

        const pool = lower + upper + digit + symbol + control + other;
        const entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

        switch (true) {
            case entropy >= 120:
                return this.STRENGTH_VERY_STRONG;
            case entropy >= 100:
                return this.STRENGTH_STRONG;
            case entropy >= 80:
                return this.STRENGTH_MEDIUM;
            case entropy >= 60:
                return this.STRENGTH_WEAK;
            default:
                return this.STRENGTH_VERY_WEAK;
        }
    }
}
