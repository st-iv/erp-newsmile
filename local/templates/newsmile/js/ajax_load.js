$(document).ready(function()
{
    $('.js-ajax-load').each(function(index, element)
    {
        var $this = $(this);
        var id = $this.attr('id');

        if(!id)
        {
            console.log('Для привязки ajax загрузчика необходимо указать id:');
            console.log(this);
            return;
        }

        if(this.tagName === 'FORM')
        {
            $(document).on('submit', '#' + id, function(e)
            {
                var $form = $(e.target);
                var areaCode = Ajax.getAreaCode($form);
                Ajax.load($form.attr('action'), areaCode, $form.serialize());
                e.preventDefault();
            });
        }
        else if(this.tagName === 'A')
        {
            $(document).on('click', '#' + id, function(e)
            {
                var $link = $(e.target);
                var areaCode = Ajax.getAreaCode($link);
                Ajax.load($link.attr('href'), areaCode);
                e.preventDefault();
            });
        }
        else
        {
            console.log('Тег ' +  this.tagName + ' не поддерживается ajax загрузчиком');
        }
    });
});

var Ajax = (function()
{
    var loadHandlers = {};

    function load(url, areaCode, data = {}, contentUpdateMethod = 'html', successHandler)
    {
        if(!areaCode) return;

        // если были переданы сериализованные параметры - переводим в объект
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
        data.sessid = General.sessid;

        console.log('Ajax.load data:');
        console.log(data);

        $.ajax({
            url: url,
            dataType: 'json',
            data: data,
            method: 'post',
            success: function(response)
            {
                var contentHtml = '';
                var $content;
                var $container;

                console.log('Ajax.load response:');
                console.log(response );

                if(response.success)
                {
                    // обновляем содержимое всех областей, содержимое которых пришло в ответе
                    if(response.content && contentUpdateMethod)
                    {
                        for(var contentAreaCode in response.content)
                        {
                            $content = $('<div>' + response.content[contentAreaCode] + '</div>');
                            $container = $content.find('div[data-ajax-area=' + contentAreaCode + ']');
                            contentHtml = ($container.length ? $container.html() : response.content[contentAreaCode]);

                            var $targetContainer = $('div[data-ajax-area="' + contentAreaCode + '"]');
                            $targetContainer[contentUpdateMethod](contentHtml);
                        }
                    }

                    /* достаем html код из контейнера */
                    contentHtml = '';

                    if(response.content[areaCode])
                    {
                        $content = $('<div>' + response.content[areaCode] + '</div>');
                        $container = $content.find('div[data-ajax-area=' + areaCode + ']');
                        contentHtml = ($container.length ? $container.html() : response.content[areaCode]);
                    }

                    // запускаем зарегистрированные обработчики загрузки области с кодом areaCode
                    if(loadHandlers[areaCode])
                    {
                        loadHandlers[areaCode].forEach(function(handler)
                        {
                            handler(response, contentHtml);
                        });
                    }

                    // запускаем обработчик успешной загрузки
                    if(typeof successHandler === 'function')
                    {
                        successHandler(response, contentHtml);
                    }
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
