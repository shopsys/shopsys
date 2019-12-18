function findLang (filePath) {
    const splittedFilePath = filePath.split('.');
    return splittedFilePath[splittedFilePath.length - 2];
};

module.exports = findLang;
