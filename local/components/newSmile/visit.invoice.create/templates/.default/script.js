;(function () {
    window.invoiceCreate = function (params) {
        this.params = params;
        this.init();
    };
    window.invoiceCreate.prototype.init = function () {
        this.loadElement();
        this.addElement();
        this.closeVisit();

    };

    window.invoiceCreate.prototype.closeVisit = function () {
        $('.invoice-close').on('click', function () {
            var invoiceId = $(this).parents('table').data('invoice-id');
            $.post(
                '',
                {
                    CLOSE_VISIT: invoiceId
                },
                function (data) {
                    location.href = "/";
                },
                'html'
            )
        });
    };

    window.invoiceCreate.prototype.loadElement = function () {
        $('.section-service').on('click', function () {
            var sectionId = $(this).data('section-id');
            var invoiceId = $(this).parents('table').data('invoice-id');
            $.post(
                '',
                {
                    LOAD_ELEMENTS: 'Y',
                    SECTION_ID: sectionId
                },
                function (data) {
                    $('#invoice-elements-' + invoiceId).html(data)
                },
                'html'
            )
        });
    };

    window.invoiceCreate.prototype.addElement = function () {
        $('.element-service').on('click', function () {
            var elementId = $(this).data('element-id');
            var invoiceId = $(this).parents('table').data('invoice-id');
            $.post(
                '',
                {
                    LOAD_ITEMS: 'Y',
                    ADD_ELEMENTS: 'Y',
                    ELEMENT_ID: elementId,
                    MEASURE: window.arToothSelect,
                    INVOICE_ID: invoiceId
                },
                function (data) {
                    $('#invoice-items-' + invoiceId).html(data)
                },
                'html'
            ).fail(function (data) {
                console.log(data);
            });
        });
    };

})();