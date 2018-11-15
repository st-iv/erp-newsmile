var CalendarDay = function(params, selector)
{
    this.params = params;
    this.$element = $(selector);

    $(document).ready(this.init.bind(this));
};

$.extend(CalendarDay.prototype, {
    init: function()
    {
        console.log(this.params);
    }
});