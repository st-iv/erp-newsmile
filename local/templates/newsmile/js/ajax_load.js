$(document).ready(function()
{
    $('.js-ajax-load').each(function()
    {
        if(this.tagName === 'FORM')
        {
            $(document).on('submit', this, function(e)
            {
                var $form = $(e.target);
                var areaCode = Ajax.getAreaCode($form);
                Ajax.load($form.attr('action'), areaCode, $form.serialize());
                e.preventDefault();
            });
        }
        else if(this.tagName === 'A')
        {
            $(document).on('click', this, function(e)
            {
                var $link = $(e.target);
                var areaCode = Ajax.getAreaCode($link);
                Ajax.load($link.attr('href'), areaCode);
                e.preventDefault();
            });
        }
    });
});

var Ajax = (function()
{
    var loadHandlers = {};

    function load(url, areaCode, data = {}, successHandler)
    {
        if(!areaCode) return;

        if(typeof data === 'string')
        {
            var serializedData = data;
            data = {};

            serializedData.split('&').forEach(function(keyPair)
            {
                var keyPairArray = keyPair.split('=');
                data[keyPairArray[0]] = keyPairArray[1];
            });
        }

        data.ajax = 'Y';
        data.area = areaCode;

        console.log('Ajax.load data:');
        console.log(data);

        $.ajax({
            url: url,
            dataType: 'json',
            data: data,
            method: 'post',
            success: function(response)
            {
                console.log('Ajax.load response:');
                console.log(response );

                if(response.success)
                {
                    if(response.content)
                    {
                        for(var contentAreaCode in response.content)
                        {
                            var $content = $('<div>' + response.content[contentAreaCode] + '</div>');
                            var $container = $content.find('div[data-ajax-area=' + contentAreaCode + ']');
                            var contentHtml = ($container.length ? $container.html() : response.content[contentAreaCode]);
                            $('div[data-ajax-area="' + contentAreaCode + '"]').html(contentHtml);
                        }
                    }

                    if(loadHandlers[areaCode])
                    {
                        loadHandlers[areaCode].forEach(function(handler)
                        {
                            handler(response);
                        });
                    }
                }

                if(typeof successHandler === 'function')
                {
                    successHandler(response);
                }
            }
        });
    }

    function getAreaCode($element)
    {
        var areaCode = $element.data('ajax-area');

        if(!areaCode)
        {
            areaCode = $element.parents('[data-is-ajax-area=Y]').data('ajax-area');

            if(!areaCode)
            {
                console.log('Ajax area code is not defined for');
                console.log($element[0]);
            }
        }

        return areaCode;
    }

    function registerLoadHandler(areaCode, handler)
    {
        if(!loadHandlers[areaCode])
        {
            loadHandlers[areaCode] = [];
        }

        loadHandlers[areaCode].push(handler);
    }

    return {
        load: load,
        getAreaCode: getAreaCode,
        registerLoadHandler: registerLoadHandler
    }
})();
