NoticeList = function (params)
{
    this.params = params || {};
    this.params.deleteQueryThrottleTime = 700;
    this.params.addQueryThrottleTime = 1000;

    this.deleteQueryTimeout = null;
    this.loadQueryTimeout = null;

    this.deletedItemsIds = [];
    this.loadItemsIds = [];

    $(document).ready(this.init.bind(this));
};

$.extend(NoticeList.prototype, {

    init: function()
    {
        var _this = this;

        //init object fields
        this.$headerWidget = $('.header_notif');
        this.$popup = $('.notif_content');
        this.$noticeList = this.$popup.find('.notif_items');
        this.ajaxAreaCode = Ajax.getAreaCode(this.$noticeList);
        this.updateItemsIds();

        //init popup
        var $popupCloseButton = this.$popup.find('.notif_content_close');
        var $showCloseButtons = this.$headerWidget.find('.notif_bell').add($popupCloseButton);

        $showCloseButtons.on('click', function()
        {
            var $menu = _this.$popup;

            $('body').toggleClass('notifOpen');

            if ($('body').hasClass('notifOpen')){
                $(document).on('mousedown.notif', function(e){
                    if(!$(e.target).hasClass('notif_bell') && !$(e.target).hasClass('notif_content') && !$menu.has(e.target).length){
                        $('body').removeClass('notifOpen');
                    }
                });
            } else {
                $(document).off('mousedown.notif');
            }
        });

        // init tabs
        this.$popup.find('.notif_tab').on('click', function()
        {
            var $this = $(this),
                dataType = $this.data('select'),
                $itemsForShow,
                $itemsForHide;

            $this.siblings('.notif_tab.tActive').removeClass('tActive');
            $this.addClass('tActive');
            if (dataType !== 'all'){
                $itemsForHide = _this.$popup.find(".notif_item[data-type!='" + dataType + "']");
                $itemsForShow = _this.$popup.find(".notif_item[data-type='" + dataType + "']");
                _this.hideNotifItems($itemsForHide);
            } else {
                $itemsForShow = _this.$popup.find(".notif_item");
            }
            _this.showNotifItems($itemsForShow);
        });

        //init scrollbar
        this.$popup.find(".notif_items").mCustomScrollbar({
            autoHideScrollbar: false
        });

        //init notice delete
        $(this.$noticeList).on('click', '.notif_item .notif_close', function(e)
        {
            _this.deleteNotice($(e.target).closest('.notif_item'));
        });

        //init finalize action
        $(window).on('beforeunload', function()
        {
            _this.finalize();
        });

        //pull event
        BX.addCustomEvent("onPullEvent-mmit.newsmile", BX.delegate(function(command,params)
        {
            if(command == 'add_notice')
            {
                this.addNotice(params.ID);
            }

        }, this));
    },

    /**
     * Удаляет уведомление на клиенте и на сервере. На сервере удаляется с задержкой, задаваемой в параметре deleteQueryThrottleTime.
     Задержка необходима для того, чтобы при быстром удалении нескольких уведомлений, на сервер отправлялся только один запрос

     * @param $noticeNode - jquery объект уведомления, которое нужно удалить
     */
    deleteNotice: function($noticeNode)
    {
        var _this = this;

        this.deletedItemsIds.push($noticeNode.data('id'));

        if(this.deleteQueryTimeout)
        {
            clearTimeout(this.deleteQueryTimeout);
        }

        this.deleteQueryTimeout = setTimeout(function()
        {
            _this.queryDeleteNotice();
            _this.deleteQueryTimeout = null;
        }, this.params.deleteQueryThrottleTime);

        $noticeNode.remove();
        this.setNoticesCount('-=1');
    },

    /**
     * Инициализирует отложенную загрузку уведомления.
     *
     * @param id - id уведомления
     */
    addNotice: function(id)
    {
        id = Number(id);

        if(!id || this.allItemsIds.includes(id) || this.loadItemsIds.includes(id)) return;

        var _this = this;
        this.loadItemsIds.push(id);

        if(this.loadQueryTimeout)
        {
            clearTimeout(this.loadQueryTimeout)
        }

        this.loadQueryTimeout = setTimeout(function()
        {
            _this.loadNotices();
            _this.loadQueryTimeout = null;
        }, this.params.addQueryThrottleTime);
    },

    /**
     * Запрашивает на сервере уведомления, id которых хранятся в массиве loadItemsIds
     */
    loadNotices: function()
    {
        var _this = this;

        if(!this.loadItemsIds) return;

        var queryData = {
            notices_ids: this.loadItemsIds
        };

        this.loadItemsIds = [];

        var ajaxSettings = {
            updateMethod: ''
        };

        Ajax.load(General.postFormAction, this.ajaxAreaCode, queryData, function(response, contentHtml)
        {
            var $newItems = $(contentHtml);

            var $notifItems = _this.getNoticesItems();

            $notifItems.parent().prepend(contentHtml);

            _this.$popup.find('.notif_tab.tActive').trigger('click');
            _this.setNoticesCount(_this.getNoticesItems().length);
            _this.updateItemsIds();
        }, ajaxSettings);
    },

    /**
     * Выполняет запрос на удаление уведомлений, id которых хранится в поле deletedItemsIds
     */
    queryDeleteNotice: function()
    {
        if (!this.deletedItemsIds) return;

        var queryData = {
            del_notices: this.deletedItemsIds
        };

        Ajax.load(General.postFormAction, this.ajaxAreaCode, queryData, null, {
            updateMethod: ''
        });
    },


    queryReadNotices: function()
    {
        var notReadNotices = [];

        this.getNoticesItems().each(function()
        {
            if(!$(this).data('is-read'))
            {
                notReadNotices.push($(this).data('id'));
            }
        });

        Ajax.load(General.postFormAction, this.ajaxAreaCode, {
            'read_notices': notReadNotices
        }, null, {updateMethod: false});
    },

    /**
     * Скрывает указанные уведомления
     * @param $items
     */
    hideNotifItems: function($items)
    {
        $items.each(function(){
            var $this = $(this);

            if (!$this.hasClass('niHidden')){
                $this.addClass('niHidden');
            }
        });
    },

    /**
     * Показывает указанные уведомления
     * @param $items
     */
    showNotifItems: function ($items)
    {
        $items.each(function(){
            var $this = $(this);

            if ($this.hasClass('niHidden')){
                $this.removeClass('niHidden');
            }
        });
    },

    /**
     * Возвращает все элементы списка уведомлений (объект jquery)
     * @returns {*}
     */
    getNoticesItems: function()
    {
        return this.$noticeList.find('.notif_item');
    },

    /**
     * Финализация при закрытии вкладки
     */
    finalize: function()
    {
        if(this.deletedItemsIds)
        {
            this.queryDeleteNotice();
        }
    },

    /**
     * Установка количества уведомлений в виджете в шапке.
     * @param count - количество уведомлений, поддерживает относительные форматы '+=X' и '-=X'
     */
    setNoticesCount: function(count)
    {
        var $counter = this.$headerWidget.find('.notif_amnt');

        if(typeof count == 'number')
        {
            $counter.text(count);
        }
        else
        {
            var countMatch = count.match(/\+=(\d+)/);
            if(countMatch)
            {
                $counter.text(Number($counter.text()) + Number(countMatch[1]));
            }
            else
            {
                countMatch = count.match(/\-=(\d+)/);
                if(countMatch)
                {
                    $counter.text(Number($counter.text()) - Number(countMatch[1]));
                }
            }
        }
    },

    /**
     * Обновлет поле allItemsIds, в котором храняется id всех загруженных уведомлений
     */
    updateItemsIds: function()
    {
        var _this = this;

        this.allItemsIds = [];
        this.getNoticesItems().each(function()
        {
            _this.allItemsIds.push($(this).data('id'));
        });
    }
});