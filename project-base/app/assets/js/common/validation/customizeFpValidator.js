const FpJsFormValidator = window.FpJsFormValidator;

// See https://github.com/shopsys/shopsys/pull/1952/files
FpJsFormValidator.__preparePrototype = FpJsFormValidator.preparePrototype;
FpJsFormValidator.preparePrototype = function (prototype, name) {
    if (prototype.data && prototype.data.form && typeof prototype.data.form.groups === 'string') {
        prototype.data.form.groups = prototype.data.form.groups.replace(/__name__/g, name);
    }
    prototype.name = prototype.name.replace(/__name__/g, name);
    prototype.id = prototype.id.replace(/__name__/g, name);

    if (typeof prototype.children === 'object') {
        for (let childName in prototype.children) {
            prototype.children[childName] = this.preparePrototype(prototype.children[childName], name);
        }
    }

    return prototype;
};
