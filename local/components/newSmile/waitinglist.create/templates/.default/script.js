;(function () {
    $(document).ready(function () {

        addSelectable();

    });

    function addSelectable() {
        $('.calendar').selectable({
            filter: ".items-calendar td",
            stop: function () {
                var strDate = [];
                $('.items-calendar td.ui-selected').each(function () {
                    strDate.push($(this).data('date'));
                });
                $('input[name="DATE"]').val(strDate);
            }
        });
    }

})();