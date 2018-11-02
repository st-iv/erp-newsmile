var PopupManager = function(maxPopupWidth = 1200)
{
    this.popups = [];
    this.maxPopupWidth = maxPopupWidth;

    this.$loader = $('<div class="popup-loader">Loading...</div>');
    this.$fader = $('<div class="popup-fade"></div>');

    $(document).ready(this.init.bind(this));
};

$.extend(PopupManager.prototype, {
    init: function()
    {
        var self = this;

        $('body').append(this.$fader);
        this.$fader.click(this.close.bind(this));

        this.baseZIndex = Number(this.$fader.css('z-index'));

        $(document).keyup(function(e)
        {
            if(e.which == 27)
            {
                self.close();
            }
        });
    },

    fadeIn: function()
    {
        this.$fader.css('z-index', this.baseZIndex + 10 * this.popups.length);
        this.$fader.addClass('shown');
    },

    fadeOut: function()
    {
        this.$fader.css('z-index', this.baseZIndex + 10 * this.popups.length);
        if(!this.popups.length)
        {
            this.$fader.removeClass('shown');
        }
    },

    showLoader: function()
    {
        this.$fader.append(this.$loader);
        this.fadeIn();
    },

    showPopup: function(content, additionalClass = '')
    {
        this.popups.push(
            new Popup(
                content,
                this.popups.length,
                this.maxPopupWidth,
                this.baseZIndex,
                this,
                additionalClass
            )
        );
        this.$loader.remove();
        this.fadeIn();
    },

    getPopup: function()
    {
        return this.popups[this.popups.length - 1].getNode();
    },

    close: function()
    {
        this.$loader.remove();
        this.popups.pop().destroy();
        this.fadeOut();
    }
});



var Popup = function(content, level, maxWidth, baseZIndex, manager, additionalClass = '')
{
    this.level = level;
    this.$node = $('<div class="popup-window ' + additionalClass + '">' + content + '</div>');
    this.$closeButton = $('<button title="Close (Esc)" type="button" class="popup-close">×</button>');
    this.manager = manager;

    this.init(maxWidth, baseZIndex);
};

$.extend(Popup.prototype, {
    init: function(maxWidth, baseZIndex)
    {
        this.$node.css('z-index', baseZIndex + 10 * (this.level + 1));

        var width = maxWidth - this.level * 60;
        this.$node.css('width', width + 'px');

        $('body').append(this.$node);

        this.$node.append(this.$closeButton);
        this.$closeButton.click(this.manager.close.bind(this.manager));
    },

    getNode: function()
    {
        return this.$node;
    },

    destroy: function()
    {
        this.$node.remove();
        this.manager.fadeOut();
    }
});