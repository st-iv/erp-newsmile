;(function () {
    window.treatmentPlan = function (params) {
        this.params = params;
        this.init();
    };
    window.treatmentPlan.prototype.init = function () {
        this.loadElement();
        this.addElement();

    };

    window.treatmentPlan.prototype.loadElement = function () {
        $('.section-service').on('click', function () {
            var sectionId = $(this).data('section-id');
            var planId = $(this).parents('table').data('plan-id');
            $.post(
                '',
                {
                    LOAD_ELEMENTS: 'Y',
                    SECTION_ID: sectionId
                },
                function (data) {
                    $('#plan-elements-' + planId).html(data)
                },
                'html'
            )
        });
    };

    window.treatmentPlan.prototype.addElement = function () {
        $('.element-service').on('click', function () {
            var elementId = $(this).data('element-id');
            var planId = $(this).parents('table').data('plan-id');
            $.post(
                '',
                {
                    LOAD_ITEMS: 'Y',
                    ADD_ELEMENTS: 'Y',
                    ELEMENT_ID: elementId,
                    MEASURE: window.arToothSelect,
                    PLAN_ID: planId
                },
                function (data) {
                    $('#plan-items-' + planId).html(data)
                },
                'html'
            )
        });
    };

})();