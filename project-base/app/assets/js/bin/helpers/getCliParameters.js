const getCliParameters = (parameters, parameterName) => {
    const prefix = `${parameterName}=`;
    return parameters
        .filter(parameter => parameter.startsWith(prefix))
        .map(parameter => parameter.slice(prefix.length));
};

module.exports = getCliParameters;
