;(function () {
    $(document).ready(function () {

        addSelectable();

    });

    var dates = [];

    function addSelectable() {
        $('.calendar').selectable({
            filter: ".items-calendar td",
            stop: function () {
                dates = [];
                $('.items-calendar td.ui-selected').each(function () {
                    dates.push($(this).data('date'));
                });

            }
        });
    }

    $('.entity-edit-form').submit(function()
    {
        var $form = $(this);

        $('input[name="DATE[]"]').val(dates[0]);

        if(dates.length > 1)
        {
            dates.forEach(function(date, index)
            {
                if(index)
                {
                    var $dateInput = $('input[name="DATE[]"]').first().clone().val(date);
                    $form.append($dateInput);
                }
            });
        }
    });
})();