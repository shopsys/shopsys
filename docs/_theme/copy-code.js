/**
 * Sanitizes diff code by removing lines starting with "-" and stripping leading "+" from added lines.
 *
 * @param {string} text - Raw diff text content
 * @returns {string} - Clean code without diff markers
 */
function sanitizeDiffCode(text) {
    const lines = text.split('\n');

    return lines
        .filter(function(line) {
            return !line.startsWith('-') && !line.startsWith('---');
        })
        .map(function(line) {
            if (line.startsWith('+') && !line.startsWith('+++')) {
                return line.substring(1);
            }

            if (line.startsWith(' ')) {
                return line.substring(1);
            }
            return line;
        })
        .join('\n')
        .trim();
}

document.addEventListener('DOMContentLoaded', function() {
    const codeBlocks = document.querySelectorAll('pre');

    codeBlocks.forEach(function(pre) {
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper';

        const button = document.createElement('button');
        button.className = 'copy-code-button';
        button.textContent = 'Copy';
        button.type = 'button';

        button.addEventListener('click', function() {
            const codeElement = pre.querySelector('code');
            let text = codeElement ? codeElement.textContent : pre.textContent;

            const isDiff = pre.classList.contains('language-diff') ||
                (codeElement && codeElement.classList.contains('language-diff'));

            if (isDiff) {
                text = sanitizeDiffCode(text);
            }

            navigator.clipboard.writeText(text).then(function() {
                button.textContent = 'Copied!';
                button.classList.add('copied');
                setTimeout(function() {
                    button.textContent = 'Copy';
                    button.classList.remove('copied');
                }, 2000);
            });
        });

        pre.parentNode.insertBefore(wrapper, pre);
        wrapper.appendChild(pre);
        wrapper.appendChild(button);
    });
});
