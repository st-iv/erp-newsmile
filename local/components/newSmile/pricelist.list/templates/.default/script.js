;(function () {
    window.priceList = function (params) {
        this.params = params;
        this.init();
    };
    window.priceList.prototype.init = function () {
        this.loadElement();
        this.addElement();
        this.initContextMenu();
    };

    window.priceList.prototype.loadElement = function () {
        $('#price-list').on('dblclick', '.section-service', function () {
            var sectionId = $(this).data('section-id');
            window.priceList.params.sectionId = sectionId;
            $.post(
                '',
                {
                    LOAD_ELEMENTS: 'Y',
                    SECTION_ID: sectionId
                },
                function (data) {
                    $('.element-list').html(data)
                },
                'html'
            )
        });
    };

    window.priceList.prototype.initContextMenu = function () {
        $.contextMenu({
            selector: "#price-list .element-list",
            items: {
                addSection: {
                    name: "Добавить раздел",
                    callback: function () {
                        $.post(
                            '/price-list/section.php',
                            {
                                SECTION_ID: window.priceList.params.sectionId
                            },
                            function (data) {
                                $('#load-content').html(data);

                            },
                            'html'
                        );
                    }
                },
                addElement: {
                    name: "Добавить услугу",
                    callback: function () {
                        $.post(
                            '/price-list/element.php',
                            {
                                SECTION_ID: window.priceList.params.sectionId
                            },
                            function (data) {
                                $('#load-content').html(data);

                            },
                            'html'
                        );
                    }
                }
            }
        });
        $.contextMenu({
            selector: "#price-list .element-list .element-service",
            items: {
                addSection: {
                    name: "Изменить услугу",
                    callback: function () {
                        $.post(
                            '/price-list/element.php',
                            {
                                ID: $(this).data('element-id')
                            },
                            function (data) {
                                $('#load-content').html(data);

                            },
                            'html'
                        );
                    }
                },
                addElement: {
                    name: "Удалить услугу",
                    callback: function () {

                    }
                }
            }
        });
        $.contextMenu({
            selector: "#price-list .element-list .section-service",
            items: {
                addSection: {
                    name: "Изменить раздел",
                    callback: function () {
                        $.post(
                            '/price-list/section.php',
                            {
                                ID: $(this).data('section-id')
                            },
                            function (data) {
                                $('#load-content').html(data);

                            },
                            'html'
                        );
                    }
                },
                addElement: {
                    name: "Удалить раздел",
                    callback: function () {
                        $.post(
                            '/price-list/section.php',
                            {
                                action: 'delete',
                                ID: $(this).data('section-id')
                            },
                            function (data) {
                                location.reload();
                            }
                        );
                    }
                }
            }
        });
    };

    window.priceList.prototype.addElement = function () {
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