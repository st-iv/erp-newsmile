HeaderSearchTitle = function (params)
{
    this.params = params;

    this.$form = $('.header_search_form');
    this.$input = this.$form.find('.search_str');
    this.$submit = this.$form.find('.search_sbmt');
    this.$popup = $('.js-header-search-content');

    this.bInputUpdated = false;
    this.bQueryInProgress = false;
    this.hasDefferedQuery = false;

    this.init();
};

$.extend(HeaderSearchTitle.prototype, {

    init: function()
    {
        var _this = this;

        Ajax.registerLoadHandler(Ajax.getAreaCode(this.$form), function(response)
        {
            _this.handleAjaxLoad();
        });

        this.$submit.click(function()
        {
            if(_this.isPopupOpened())
            {
                _this.closeSearchPopup();
            }
            else if(!_this.submit())
            {
                _this.openSearchPopup();
            }
        });

        this.$input.on('input', function()
        {
            _this.bInputUpdated = true;

            if(_this.isPopupOpened())
            {
                _this.submit();
            }
        });

        $(document).keyup(function(e)
        {
            if(e.which == 27)
            {
                _this.closeSearchPopup();
            }
        });

    },

    initScrollbars: function()
    {
        this.$popup.find('.search_res_cont').mCustomScrollbar({
            autoHideScrollbar: false
        });
    },

    toggleSearchPopup: function()
    {
        var $menu = $('.search_content');
        var $searchForm = $('.header_search_form');

        $('body').toggleClass('searchOpen');

        if ($('body').hasClass('searchOpen'))
        {
            $(document).on('mousedown.search', function(e){
                if(!$searchForm.has(e.target).length && !$(e.target).hasClass('search_sbmt') && !$(e.target).hasClass('search_content') && !$menu.has(e.target).length)
                {
                    $('body').removeClass('searchOpen');
                }
            });
        }
        else
        {
            $(document).off('mousedown.search');
        }
    },

    openSearchPopup: function()
    {
        if(!this.isPopupOpened())
        {
            this.toggleSearchPopup();
        }
    },

    closeSearchPopup: function()
    {
        if(this.isPopupOpened())
        {
            this.toggleSearchPopup();
        }
    },

    isPopupOpened: function()
    {
        return $('body').hasClass('searchOpen');
    },

    submit: function()
    {
        var result = false;

        if(this.bInputUpdated)
        {
            if(this.$input.val().length >= this.params.minQueryLength)
            {
                if(this.queryInProgress)
                {
                    this.hasDefferedQuery = true;
                    result = false;
                }
                else
                {
                    this.queryInProgress = true;
                    this.$form.submit();
                    this.hasDefferedQuery = false;
                    this.bInputUpdated = false;

                    result = true;
                }
            }
            else
            {
                this.cleanPopupWindow();
            }

        }

        return result;
    },

    handleAjaxLoad: function()
    {
        this.initScrollbars();
        this.openSearchPopup();
        this.queryInProgress = false;

        if(this.hasDefferedQuery)
        {
            this.submit();
        }
    },

    cleanPopupWindow: function()
    {
        this.$popup.find('.search_result').html('');
    }
});