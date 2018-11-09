var Ajax = (function()
{
    var loadHandlers = {};
    var loadedScripts = {};
    var loaders = [];
    var inProgress = [];
    var throttleModes = {
        blockAll: 1,
        passLastOnly: 2
    };

    function initLoaders()
    {
        clearLoaders();

        $('.js-ajax-load').each(function(index, element)
        {
            if(this.tagName === 'FORM')
            {
                $(this).on('submit.ajaxload', function(e)
                {
                    var $form = $(this);
                    var areaCode = Ajax.getAreaCode($form);
                    Ajax.load($form.attr('action'), areaCode, $form.serialize());
                    e.preventDefault();
                });
            }
            else if(this.tagName === 'A')
            {
                $(this).on('click.ajaxload', function(e)
                {
                    var $link = $(this);
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
    }

    function clearLoaders()
    {
        loaders.forEach(function(loader)
        {
            $(loader).off('submit.ajaxload click.ajaxload');
        });

        loaders = [];
    }


    /**
     * Выполняет загрузку указанной области ajax запросом
     * @param url
     * @param areaCode - код ajax области, которую необходимо загрузить
     * @param data - массив параметров
     * @param successHandler - обработчик успешной загрузки, в качестве первого параметра получает ответ сервера, вторым парметром
     * @param settings - объект с дополнителньыми настройками:
     *                  updateMethod - метод обновления загружаемой области - название jquery функции для вставки нового контента
     *                  в контейнер области. Например, html, prepend, append. При пустом значении загружаемая область не будет обновлена
     *                  загруженный html код (извлеченный из контейнера области)
     *
     *                  throttleMode - режим блокировки загрузки данной области во время выполнения запроса. По умолчанию passLastOnly
     *                      blockAll - игнорирование всех запросов
     *                      passLastOnly - выполнение только последнего запроса
     *
     */
    function load(url, areaCode, data = {}, successHandler = null, settings = {})
    {
        var defaults = {
            updateMethod: 'html',
            throttleMode: throttleModes.passLastOnly
        };

        settings = $.extend(defaults, settings);

        if(!inProgress[areaCode])
        {
            console.log('Ajax.load load');

            inProgress[areaCode] = true;
            var ajaxConfig = getAjaxQueryConfig(url, areaCode, data,  settings.updateMethod, successHandler);
            $.ajax(ajaxConfig);
        }
        else
        {
            console.log('Ajax.load block');

            if(settings.throttleMode === throttleModes.passLastOnly)
            {
                // запомнить последний запрос
            }
        }
    }

    /**
     * Выполняет загрузку указанной области ajax запросом и выводит результат в popup окне
     * @param url
     * @param areaCode
     * @param data
     * @param successHandler - обработчик успешной загрузки,  Function( String contentHtml, JQueryObject $popup )
     *
     * @param additionalClass
     */
    function loadPopup(url, areaCode, data = {}, successHandler = null, additionalClass = '')
    {
        var ajaxSettings = {
            updateMethod: false
        };

        load(url, areaCode, data, function(response)
        {
            window.popupManager.showPopup(response.content[areaCode], additionalClass);

            if(typeof successHandler === 'function')
            {
                successHandler(response.content[areaCode], window.popupManager.getPopup());
            }
        }, ajaxSettings);
    }

    /**
     * Собирает объект конфигурации ajax запроса
     * @param url
     * @param areaCode
     * @param data
     * @param contentUpdateMethod
     * @param successHandler
     * @returns {*}
     */
    function getAjaxQueryConfig(url, areaCode, data, contentUpdateMethod = 'html', successHandler = null)
    {
        if(!areaCode) return null;

        // если были переданы сериализованные параметры - переводим в объект
        if(typeof data === 'string')
        {
            var serializedData = data;
            data = {};

            serializedData.split('&').forEach(function(keyPair)
            {
                var keyPairArray = keyPair.split('=');

                if(typeof data[keyPairArray[0]] === 'undefined')
                {
                    data[keyPairArray[0]] = keyPairArray[1];
                }
                else
                {
                    data[keyPairArray[0]] = keyPairArray[1];
                }
            });
        }

        // простые объекты переводим в FormData
        if($.isPlainObject(data))
        {
            var dataObj = Object.assign(data);
            data = new FormData();

            for(var fieldName in dataObj)
            {
                data.append(fieldName, dataObj[fieldName]);
            }
        }

        if(!(data instanceof FormData))
        {
            console.log('При подготовке ajax запроса не удалось преобразовать данные к FormData');
            return;
        }

        data.set('ajax', 'Y');
        data.set('area', areaCode);
        data.set('sessid', General.sessid);

        return  {
            url: url,
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            method: 'post',
            success: handleAjaxResponse.bind(null, areaCode, successHandler, contentUpdateMethod),
            complete: function()
            {
                inProgress[areaCode] = false;
            }
        };
    }

    /**
     * Загружает и выполняет js скрипты (только если они не были загружены этим методом ранее)
     * @param scripts - массив url скриптов
     * @param callback - функция, вызываемая после успешной загрузки всех скриптов
     */
    function loadScripts(scripts, callback)
    {
        var scriptsQueueSize = 0;

        scripts.forEach(function (scriptUrl)
        {
            if(!loadedScripts[scriptUrl])
            {
                scriptsQueueSize++;

                $.getScript(scriptUrl, function ()
                {
                    scriptsQueueSize--;

                    loadedScripts[scriptUrl] = true;

                    if (!scriptsQueueSize && (typeof callback === 'function'))
                    {
                        callback();
                    }
                });
            }
        });

        if(!scriptsQueueSize)
        {
            callback();
        }
    }

    function loadNode($node, data = {}, success = null, settings = {})
    {
        var url;

        switch ($node[0].tagName)
        {
            case 'FORM':
                url = $node.attr('action');
                if(!data)
                {
                    data = new FormData($node[0]);
                }
                break;

            case 'A':
                url = $node.attr('href');
                break;

            default:
                url = $node.data('action')
        }

        Ajax.load(url, getAreaCode($node), data, success, settings);
    }

    function processAjaxResponse(areaCode, successHandler, contentUpdateMethod, response)
    {
        var contentHtml;

        // обновляем все области, содержимое которых пришло в ответе
        if(response.content && contentUpdateMethod)
        {
            for(var contentAreaCode in response.content)
            {
                contentHtml = unpackAreaContent(response.content[contentAreaCode], contentAreaCode);
                var $targetContainer = $('div[data-ajax-area="' + contentAreaCode + '"]');
                $targetContainer[contentUpdateMethod](contentHtml);
            }
        }

        /* достаем html код из контейнера */
        contentHtml = '';

        if(response.content[areaCode])
        {
            contentHtml = unpackAreaContent(response.content[areaCode], areaCode);
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

        Ajax.initLoaders();
    }

    /**
     * Обрабатывает результат ajax запроса
     * @param areaCode
     * @param successHandler
     * @param contentUpdateMethod
     * @param response
     */
    function handleAjaxResponse(areaCode, successHandler, contentUpdateMethod, response)
    {
        console.log('Ajax.load response:');
        console.log(response );

        if(response.success)
        {
            if(response.scripts)
            {
                // если в ответе указаны скрипты для динамической подгрузки, то сначала грузим их
                loadScripts(
                    response.scripts,
                    processAjaxResponse.bind(null, areaCode, successHandler, contentUpdateMethod, response)
                );
            }
            else
            {
                processAjaxResponse(areaCode, successHandler, contentUpdateMethod, response);
            }
        }
    }


    /**
     * Вытаскивает html код указанной области из контейнера
     * @param rawContentHtml - html код, в котором содержится контейнер области
     * @param areaCode - код области
     * @returns string
     */
    function unpackAreaContent(rawContentHtml, areaCode)
    {
        var $content = $('<div>' + rawContentHtml + '</div>');
        var $container = $content.find('div[data-ajax-area="' + areaCode + '"]');
        return ($container.length ? $container.html() : rawContentHtml);
    }


    function getAreaCode($element, checkParents = true)
    {
        var areaCode = $element.data('ajax-area');

        if(!areaCode && checkParents)
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
        initLoaders: initLoaders,
        load: load,
        loadPopup: loadPopup,
        loadNode: loadNode,
        loadScripts: loadScripts,
        getAreaCode: getAreaCode,
        registerLoadHandler: registerLoadHandler
    }
})();

$(document).ready(Ajax.initLoaders);