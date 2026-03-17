const appendSourceToDirective = (cspHeader: string, directiveName: string, source: string): string => {
    const directives = cspHeader.split(';');

    for (let index = 0; index < directives.length; index++) {
        const trimmedDirective = directives[index].trim();

        if (!trimmedDirective.startsWith(`${directiveName} `)) {
            continue;
        }

        directives[index] = trimmedDirective.includes(source) ? trimmedDirective : `${trimmedDirective} ${source}`;

        return directives.map((directive) => directive.trim()).join('; ');
    }

    return cspHeader;
};

export const applyStorefrontDevelopmentCspAppendices = (cspHeader: string): string =>
    appendSourceToDirective(cspHeader, 'script-src', "'unsafe-eval'");
