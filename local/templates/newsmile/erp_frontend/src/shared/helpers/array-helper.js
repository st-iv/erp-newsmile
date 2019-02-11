let ArrayHelper = {
    modifyPrototype: function()
    {
        [diff, unique].forEach(method =>
        {
            if(Array.prototype[method.name])
            {
                console.warn('Перезаписан существующий метод Array.prototype.' + method.name);
            }

            Array.prototype[method.name] = method;
        });
    }
};

function diff(array)
{
    let curArray = this.unique();

    return curArray.filter(value =>
    {
        return (array.indexOf(value) === -1);
    });
}

function unique()
{
    return this.filter((value, index) =>
    {
        return (this.indexOf(value) === index);
    });
}

export default ArrayHelper;