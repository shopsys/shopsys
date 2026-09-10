const serializeConsoleArgument = (argument: unknown): string => {
    if (typeof argument === 'object' && argument !== null) {
        try {
            return JSON.stringify(argument, null, 2) ?? String(argument);
        } catch {
            return String(argument);
        }
    }

    return String(argument);
};

export const formatConsoleArguments = (args: unknown[]): string => {
    const [format, ...values] = args;

    if (typeof format !== 'string') {
        return args.map(serializeConsoleArgument).join(' ');
    }

    let valueIndex = 0;
    const formattedMessage = format.replace(/%[cdfioOs%]/g, (placeholder) => {
        if (placeholder === '%%') {
            return '%';
        }

        if (valueIndex >= values.length) {
            return placeholder;
        }

        const value = values[valueIndex++];

        return placeholder === '%c' ? '' : serializeConsoleArgument(value);
    });
    const remainingValues = values.slice(valueIndex).map(serializeConsoleArgument);

    return [formattedMessage, ...remainingValues].join(' ');
};
