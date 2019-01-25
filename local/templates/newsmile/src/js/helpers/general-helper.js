let lastId = 0;

let GeneralHelper = {

    getParamsObject: function(serializedParams)
    {
        var result = {};
        serializedParams.split('&').forEach(function(keyValuePair)
        {
            var pairArray = keyValuePair.split('=');
            var paramName = pairArray[0];
            var paramValue = decodeURI(pairArray[1]);

            if(result[paramName])
            {
                if(Array.isArray(result[paramName]))
                {
                    result[paramName].push(paramValue);
                }
                else
                {
                    result[paramName] = [result[paramName], paramValue];
                }
            }
            else
            {
                result[paramName] = paramValue;
            }
        });

        return result;
    },

    serializeInObject: function(formNode)
    {
        return this.getParamsObject($(formNode).serialize());
    },

    getFio: function(person)
    {
        var fio = person.lastName + ' ' + person.name[0].toUpperCase() + '.';
        if(person.secondName)
        {
            fio += ' ' + person.secondName[0].toUpperCase() + '.';
        }

        return fio;
    },

    getFullName: function(person)
    {
        var fullName = person.lastName + ' ' + person.name;
        if (person.secondName)
        {
            fullName += ' ' + person.secondName;
        }

        return fullName;
    },

    getCountString: function(number, variants)
    {
        var units = number % 10;
        var variantIndex = 2;

        if(units === 1)
        {
            variantIndex = 0;
        }
        else if((units >= 2) && (units <= 4))
        {
            variantIndex = 1;
        }

        return number + ' ' + variants[variantIndex];
    },

    /**
     * Клонирует массивы и объекты
     * @param arrayOrObject
     * @returns {any}
     */
    clone: function(arrayOrObject)
    {
        return JSON.parse(JSON.stringify(arrayOrObject));
    },

    getCamelCase: function(str, isUpper = true)
    {
        if(!str.length) return str;

        /*separators = separators || ['_', '-', '/'];
        var regExp = new RegExp('[\\' + separators.join('\\') + ']([A-Za-z])', 'g');*/

        var result = str.toLowerCase().replace(/[^A-Z^a-z]([A-Za-z])/g, function(match, p1)
        {
            return p1.toUpperCase();
        });

        if(isUpper)
        {
            result = result[0].toUpperCase() + result.substr(1);
        }

        return result;
    },

    uniqueId: function(prefix = 'generated-id-')
    {
        return ++lastId + prefix;
    },

    ucfirst: function(str)
    {
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1, str.length-1);
    },

    forEachObj: function(object, handler, from = null, end = null)
    {
        var bActiveKey = (from === null);

        for(let key in object)
        {
            if((end !== null) && (key === end)) break;

            if(key === from)
            {
                bActiveKey = true;
            }

            if(bActiveKey)
            {
                if(object.hasOwnProperty(key))
                {
                    handler(object[key], key, object);
                }
            }
        }
    },

    filterObj: function(object, handler)
    {
        const result = {};
        this.forEachObj(object, function(value, key, object)
        {
            if(handler(value, key, object))
            {
                result[key] = value;
            }
        });

        return result;
    },

    mapObj: function(object, handler)
    {
        let result = [];

        this.forEachObj(object, (value, key) =>
        {
            result.push(handler(value, key, object));
        });

        return result;
    },

    sortObj: function(object)
    {
        let result = {};

        Object.keys(object).sort().forEach(key =>
        {
            result[key] = object[key];
        });

        return result;
    },

    isEqual: function(var1, var2)
    {
        var result = true;

        if((typeof var1 === 'object') && (typeof var2 === 'object'))
        {
            if(Object.keys(var1).length === Object.keys(var2).length)
            {
                for (let key in var1)
                {
                    if(!var1.hasOwnProperty(key)) continue;

                    if(!this.isEqual(var1[key], var2[key]))
                    {
                        result = false;
                        break;
                    }
                }
            }
            else
            {
                result = false;
            }
        }
        else
        {
            result = var1 === var2;
        }

        return result;
    },
};

export default GeneralHelper