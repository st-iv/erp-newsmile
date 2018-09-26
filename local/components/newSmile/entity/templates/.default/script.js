;(function () {
    window.entitiesList = function (params) {
        this.params = params;
        this.init();
    };
    window.entitiesList.prototype.init = function () {
        this.initContextMenu();
        this.initLoadElement();
    };

    window.entitiesList.prototype.initLoadElement = function () {
        var _this = this;

        $('#entities-list').on('dblclick', '.section-service', function (e) {
            $.post(
                $(this).data('section-url'),
                {
                    LOAD_ELEMENTS: 'Y'
                },
                function (data) {

                    $('.element-list').html(data)
                },
                'html'
            )

            e.stopPropagation();
        });
    };

    window.entitiesList.prototype.initContextMenu = function () {
        var _this = this;

        $.contextMenu({
            selector: "#entities-list .element-list",
            items: {
                addSection: {
                    name: "Добавить группу",
                    callback: function () {
                        $.post(
                            _this.params.ajaxUrlAddGroup,
                            {
                                ajax: 'Y',
                                action: 'add'
                            },
                            function (data) {
                                $('#load-content').html(data);
                                _this.initFormsAjaxSend(true);
                            },
                            'html'
                        );
                    }
                },
                addElement: {
                    name: "Добавить элемент",
                    callback: function () {
                        $.post(
                            _this.params.ajaxUrlAddElement,
                            {
                                ajax: 'Y'
                            },
                            function (data) {
                                $('#load-content').html(data);
                                _this.initFormsAjaxSend(true);
                            },
                            'html'
                        );
                    }
                }
            }
        });
        $.contextMenu({
            selector: "#entities-list .element-list .element-service",
            items: {
                addSection: {
                    name: "Изменить элемент",
                    callback: function () {
                        var editUrl = $(this).data('edit-url');
                        if(editUrl)
                        {
                            $.post(
                                editUrl,
                                {
                                    ajax: 'Y'
                                },
                                function (data) {
                                    $('#load-content').html(data);
                                    _this.initFormsAjaxSend();
                                },
                                'html'
                            );
                        }

                    }
                },
                addElement: {
                    name: "Удалить элемент",
                    callback: function () {
                        var editUrl = $(this).data('edit-url');

                        if(editUrl)
                        {
                            $.post(
                                editUrl,
                                {
                                    action: 'delete',
                                    ajax: 'Y',
                                    sessid: _this.params.sessid
                                },
                                function (data)
                                {
                                    location.reload();
                                }
                            );
                        }
                    }
                }
            }
        });
        $.contextMenu({
            selector: "#entities-list .section-service",
            items: {
                addSection: {
                    name: "Изменить группу",
                    callback: function (action, params, event) {
                        var editUrl = $(this).data('edit-url');

                        if(editUrl)
                        {
                            $.post(
                                editUrl,
                                {
                                    ajax: 'Y'
                                },
                                function (data) {
                                    $('#load-content').html(data);
                                    _this.initFormsAjaxSend();
                                },
                            );
                        }
                    }
                },
                addElement: {
                    name: "Удалить группу",
                    callback: function () {
                        var editUrl = $(this).data('edit-url');

                        if(editUrl)
                        {
                            $.post(
                                editUrl,
                                {
                                    action: 'delete',
                                    ajax: 'Y',
                                    sessid: _this.params.sessid
                                },
                                function (data)
                                {
                                    location.reload();
                                }
                            );
                        }
                    }
                }
            }
        });
    };

    window.entitiesList.prototype.addElement = function () {
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

    window.entitiesList.prototype.initFormsAjaxSend = function (bReload = false)
    {
        $('#load-content form').submit(function(e)
        {
            var $form = $(this);
            var data = $form.serialize();

            $.post(
                $form.attr('action'),
                data,
                function(html)
                {
                    if(bReload)
                    {
                        location.reload();
                    }
                    else
                    {
                        $form.html(html);
                    }
                }
            );
            e.preventDefault();
        });
    }

})();