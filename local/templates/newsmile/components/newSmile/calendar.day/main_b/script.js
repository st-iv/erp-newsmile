var CalendarDay = function(params)
{
    var self = this;
    this.doctors = params.doctors;
    this.$table = $(params.selector);
    this._popupOpen = false;


    $(document).ready(function()
    {
        self.init();
    });
};

$.extend(CalendarDay.prototype, {
    init: function()
    {
        var self = this;

        this.$table.find('.dayCalendar_interval').not('.emptyI').each(function()
        {
            var $this = $(this);
            self.initColors($this);
            self.initPopup($this);
            self.initOperations($this);
        });
    },

    initColors: function($interval)
    {
        var doctor = this.doctors[$interval.data('doctor-id')];

        $interval.css('border-color', this.lightenColor(doctor.COLOR, 13));
        $interval.css('color', this.darkenColor(doctor.COLOR, 50));

        var backgroundLightenCoef = ($interval.data('is-visit') ? 30 : 42);
        $interval.css('background-color', this.lightenColor(doctor.COLOR, backgroundLightenCoef));
    },

    initPopup: function($interval)
    {
        var $intervalPopup = $interval.find('.dayCalendar_popup');
        var self = this;

        var	popper = new Popper($interval, $intervalPopup,{
            placement: 'bottom-start',
            onUpdate: function(data){
                if (data.instance.options.placement == "bottom-start"){
                    if (data.offsets.popper.left  != data.offsets.reference.left){
                        data.instance.options.placement = "bottom-end";
                        $(data.instance.reference).addClass('dClndr_popup_to_rght');
                        data.instance.scheduleUpdate();
                    }
                } else if (data.instance.options.placement == "bottom-end") {
                    if ((data.offsets.popper.left + data.offsets.popper.width) != (data.offsets.reference.left + data.offsets.reference.width)){
                        data.instance.options.placement = "bottom-start";
                        $(data.instance.reference).removeClass('dClndr_popup_to_rght');
                        data.instance.scheduleUpdate();
                    }
                }
            },
            modifiers: {
                preventOverflow: {
                    enabled: true,
                    boundariesElement: document.body
                },
                offset: {
                    enabled: true
                }
            }
        });

        if($interval.data('is-visit'))
        {
            $interval.on('mouseenter', function(){
                if (!self._popupOpen) {
                    var $this = $(this);
                    $this.removeClass('dClndr_pshowmenu');
                    $this.children('.dayCalendar_popup').fadeIn({
                        duration: 150,
                        queue: false,
                        start: function(){
                            $this.addClass('dClndr_pshowed');
                        }
                    });
                    popper.update();
                }
            });
            $interval.on('mouseleave', function(){
                if (!self._popupOpen) {
                    var $this = $(this);
                    $this.children('.dayCalendar_popup').fadeOut({
                        duration: 150,
                        queue: false,
                        complete: function(){
                            $this.removeClass('dClndr_pshowed');
                        }
                    });

                }
            });
        }

        $interval.on('click', function(e){
            var $this = $(this);

            if(($this.is(e.target) || $this.children('span').is(e.target)) && !self._popupOpen){
                if (!$this.hasClass('dClndr_pshowmenu')) {
                    $this.addClass('dClndr_pshowmenu');
                    $this.children('.dayCalendar_popup').fadeIn({
                        duration: 150,
                        queue: false,
                        start: function(){
                            $this.addClass('dClndr_pshowed');
                        }
                    });
                    self._popupOpen = true;
                    popper.update();

                    $(document).on('mousedown.dclndrpopup', function(e){
                        if(!$this.has(e.target).length && !$this.is(e.target)){
                            self._popupOpen = false;
                            $this.children('.dayCalendar_popup').fadeOut({
                                duration: 150,
                                queue: false,
                                complete: function(){
                                    $this.removeClass('dClndr_pshowed');
                                    $this.removeClass('dClndr_pshowmenu');
                                }
                            });

                            $(document).off('mousedown.dclndrpopup');
                        }
                    });
                }
            } else if (($this.is(e.target) || $this.children('span').is(e.target)) && self._popupOpen) {
                $this.removeClass('dClndr_pshowmenu');
                popper.scheduleUpdate();
                self._popupOpen = false;
                $(document).off('mousedown.dclndrpopup');
            }
        });

        $intervalPopup.find('.dClndr_phasmenu')
            .on('mouseenter', function(){
                $interval.addClass('dClndr_submemu_act');
            })
            .on('mouseleave', function(){
                $interval.removeClass('dClndr_submemu_act');
            });
    },

    initOperations: function($interval)
    {
        var self = this;

        $interval.find('.dClndr_popup_menu .dClndr_pmenu > li').not('.dClndr_phasmenu').click(function()
        {
            var operationCode = $(this).data('operation-code');
            var handlerName = 'handleOperation' + operationCode;

            if(operationCode && (typeof self[handlerName] === 'function'))
            {
                self[handlerName].apply(self, [$interval]);
            }
            else
            {
                console.log('DayCalendar: не найден обработчик операции ' + operationCode);
            }
        });

        $interval.find('.dClndr_popup_menu .dClndr_psubmenu > li').not('.dClndr_phasmenu').click(function()
        {
            var operationCode = $(this).closest('.dClndr_phasmenu').data('operation-code');
            var handlerName = 'handleOperation' + operationCode;

            if(operationCode && (typeof self[handlerName] === 'function'))
            {
                self[handlerName].apply(self, [$interval, $(this).data('variant-code')]);
            }
            else
            {
                console.log('DayCalendar: не найден обработчик операции ' + operationCode);
            }
        });
    },

    lightenColor: function(initColor, value)
    {
        var color = tinycolor(initColor),
            coef = 1 - Math.pow((color.getBrightness() / 255), 3);

        return color.lighten(value * coef).toString();
    },

    darkenColor: function(initColor, value)
    {
        var color = tinycolor(initColor),
            coef = color.getBrightness() / 255;

        return color.darken(value * coef).toString();
    },

    /* обработчики операций с интервалами */
    handleOperationCancelVisit: function($interval)
    {

    },

    handleOperationChangeDoctor: function($interval, doctorId)
    {

    }
});