HeaderSearchTitle = function (params)
{
    this.params = params;

    this.$form = $('.header_search_form');
    this.$input = $('.header_search_form .search_str');
    this.$submit = $('.header_search_form .search_sbmt');

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

        if(this.bInputUpdated && (this.$input.val().length >= this.params.minQueryLength))
        {
            if(this.queryInProgress)
            {
                this.hasDefferedQuery = true;
            }
            else
            {
                this.queryInProgress = true;
                this.$form.submit();
            }

            result = true;
        }

        return result;
    },

    handleAjaxLoad: function()
    {
        this.openSearchPopup();
        this.bInputUpdated = false;
        this.queryInProgress = false;

        if(this.hasDefferedQuery)
        {
            this.submit();
            this.hasDefferedQuery = false;
        }
    }
});