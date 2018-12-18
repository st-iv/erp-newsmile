$(document).ready(function(){

	$('.form__fields-wrapper').mCustomScrollbar();

	$('.new-visit__table-wrapper').mCustomScrollbar();

	$('#advertisment-field').select2();

	$('.form__input--phone').mask('+7 (000) 000 00 00');

	//$('#date-filed').mask('00.00.0000');

	$('.form__add-field-btn').on('click', function(){
		cloneBlock($('.form__add-field-btn').closest('.form__block--phone').find('#phone-field'));
	});

	/*$('.form__label').on('click', function(){
		$(this).next($('.form__input')).focus();
	});

	$('.form__input').on('focus', function(){
		$(this).closest($('.form__wrapper')).find($('.form__label')).addClass('form__label--focus');
	});

	$('.form__input').on('blur', function(){
		if ($(this).val().length == 0) {
			$(this).closest($('.form__wrapper')).find($('.form__label')).removeClass('form__label--focus');
		}
	});*/

	$('.status__menu-btn').on('click', function(){
		dropdownElementInFixedBlock($('.status'), $('.status__list'), $(this).next($('.status__menu')), 'status__menu--active');
	});

	$(document).mouseup(function (e){
		closeDropdownElementInFixedBlock($('.status__menu'), 'status__menu--active', e.target);
	});

	$('.status__list--close').on('click', function(){
		$(this).removeClass('status__list--open');
		$(this).next($('.status__list--active')).addClass('status__list--open');
	});

	// Открывает окно записи на новый прием по клику на слово "Раписание",
	// в дальнейшем надо изменить на нажатие на нужный элемент
	$('.shld_filter_title').on('click', function(){
		$(this).closest($('body')).find($('.new-visit')).addClass('new-visit--active');
	});

	$('.new-visit__close-btn').on('click', function(){
		var strAnimationDuration = $(this).closest($('.new-visit')).find('.new-visit__wrapper').css('animation-duration'),
		// значение "bottom", преобразованное в число
			numAnimationDurationValue = Number(strAnimationDuration.replace(strAnimationDuration.substr(strAnimationDuration.length - 1), '')) * 1000;
		closeElement($(this).closest($('.new-visit')), 'new-visit--active', 'new-visit--close', numAnimationDurationValue);
	});

	$('.status__close-btn').on('click', function(){
		$(this).parent('.status__list--active').removeClass('status__list--open');
		$(this).parent('.status__list--active').prev($('.status__list--close')).addClass('status__list--open');
	});

	$('.notify__close-btn').on('click', function(){
		var self = $(this);
		$(this).parent($('.notify__item')).removeClass('notify__item--open');
			$(this).parent($('.notify__item')).animate({
			  height: 0,
			  paddingTop: 0,
			  paddingBottom: 0,
			}, 500, function() {
				$(self).parent($('.notify__item')).remove();
			});
	});

	$('.menu_btn_shld').on('click', function(){
		var $menu = $('.left_menu_content'),
		$button = $(this);

		$menu.toggleClass('showIt');
		$button.toggleClass('bActive');

		if ($menu.hasClass('showIt')){
			$(document).on('mousedown.menu', function(e){
			    if(!$(e.target).hasClass('menu_btn_shld') && !$(e.target).hasClass('left_menu_content') && !$menu.has(e.target).length){
					$menu.removeClass('showIt');
					$button.removeClass('bActive');
			    }
			});			
		} else {
			$(document).off('mousedown.menu');
		}
	});

	/*
	MOVED TO local/templates/newsmile/components/newSmile/notice.list/main/script.js
	function hideNotifItems($items){
		$items.each(function(){
			var $this = $(this);

			if (!$this.hasClass('niHidden')){
				$this.addClass('niHidden');
			}
		});
	}
	function showNotifItems($items){
		$items.each(function(){
			var $this = $(this);

			if ($this.hasClass('niHidden')){
				$this.removeClass('niHidden');
			}
		});
	}*/
	/*$('.notif_content .notif_tab').on('click', function(){
		var $this = $(this),
		dataType = $this.data('select'),
		$itemsForShow,
		$itemsForHide;

		if (!$this.hasClass('tActive')){
			$this.siblings('.notif_tab.tActive').removeClass('tActive');
			$this.addClass('tActive');
			if (dataType !== 'all'){
				$itemsForHide = $(".notif_content .notif_item[data-type!='" + dataType + "']"); 
				$itemsForShow = $(".notif_content .notif_item[data-type='" + dataType + "']"); 
				hideNotifItems($itemsForHide);
			} else {
				$itemsForShow = $(".notif_content .notif_item"); 
			}
			showNotifItems($itemsForShow);
		}
	});*/

	/*$('.notif_item .notif_close').on('click', function(){
		$(this).closest('.notif_item').remove();
	});	*/

	/*$(".notif_items").mCustomScrollbar({ // DELETED ", .search_res_cont"
		autoHideScrollbar: false
	});*/

	/*$('.notif_bell, .notif_content_close').on('click', function(){
		var $menu = $('.notif_content');

		$('body').toggleClass('notifOpen');

		if ($('body').hasClass('notifOpen')){
			$(document).on('mousedown.notif', function(e){
			    if(!$(e.target).hasClass('notif_bell') && !$(e.target).hasClass('notif_content') && !$menu.has(e.target).length){
					$('body').removeClass('notifOpen');
			    }
			});			
		} else {
			$(document).off('mousedown.notif');
		}
	});*/

	//MOVED TO local/templates/newsmile/components/newSmile/search.title/header/script.js
	/*$('.search_sbmt').on('click', function(){
		var $menu = $('.search_content');

		$('body').toggleheader_search_formClass('searchOpen');

		if ($('body').hasClass('searchOpen')){
			$(document).on('mousedown.search', function(e){
			    if(!$(e.target).hasClass('search_sbmt') && !$(e.target).hasClass('search_content') && !$menu.has(e.target).length){
					$('body').removeClass('searchOpen');
			    }
			});			
		} else {
			$(document).off('mousedown.search');
		}
	});*/

    //MOVED TO local/templates/newsmile/components/newSmile/calendar.filter/main/script.js
    /*$.widget( "custom.iconselectmenu", $.ui.selectmenu, {
		_renderItem: function(ul, item) {
			var li = $("<li>"),
			wrapper = $("<div>", {text: item.label});

			if (item.disabled) {
				li.addClass("ui-state-disabled");
			}

			if (item.element.attr("data-color") == "fff") {
				li.addClass("df-doctor");
			}

			$( "<span>", {
				style: "background-color: #" + item.element.attr("data-color") +";",
				"class": "doctor-color"
			}).prependTo(wrapper);

			return li.append(wrapper).appendTo(ul);
		},
		_renderButtonItem: function( item ) {
			var buttonItem = $( "<span>", {
				"class": "ui-selectmenu-text"
			})

			this._setText( buttonItem, item.label );

			if (item.element.attr("data-color") == "fff") {
				buttonItem.addClass("df-doctor");
			}

			$( "<span>", {
				style: "background-color: #" + item.element.attr("data-color") +";",
				"class": "doctor-color"
			}).prependTo(buttonItem);

			return buttonItem;
		}
    });
 
    $("#doctor").iconselectmenu().iconselectmenu("menuWidget");

	$("#speс").selectmenu();*/



	// MOVED TO local/templates/newsmile/components/newSmile/calendar/main/script.js
	/*$( "#left-calendar" ).customCalendar({
		dateFrom: '2018-05-24',
		dateTo: '2018-08-24',
		curDate: '2018-05-28',
		defDateColor: 'eaeaea',
		dateData : {
			'2018-05-28': {
				color: 'ffb637',
				patients: 7,
				timeFree: '11:30',
				timeAvlble: '20:30'
			},
			'2018-06-14': {
				color: 'd2d512',
				patients: 8,
				timeFree: '11:30',
				timeAvlble: '20:30'
			}
		}
	});*/
});

/* CUSTOM */

$(document).ready(function()
{
    var $headerClock = $('#header-clock');
    var tsDiff = Number($headerClock.data('ts')) - Date.now();
    var serverDateTime = new Date(Date.now() + tsDiff);

    setTimeout(function()
    {
        updateClock($headerClock);
        setInterval(updateClock.bind(null, $headerClock), 60000);
    }, (60 - serverDateTime.getSeconds()) * 1000);


    function updateClock($clock)
    {
        $clock.text(General.Date.formatTime(Date.now() + tsDiff));
    }
});

//var popupManager = new PopupManager();

// Измеряет высоту выпадающего меню в фиксированном блоке и открывает его вверх, если оно будет обрезаться границей экрана, и вниз, если обрезаться не будет
// @param $wrapper (jquery object) - фиксированный блок-обертка
// @param $parentBlock (jquery object) - родительский блок выпадающего элемента
// @param $element (jquery object) - выпадающий блок
// @param strNewClass (string) - класс для появления выпадающего блока

function dropdownElementInFixedBlock ($wrapper, $parentBlock, $element, strNewClass) {
    'use strict';
    // позиция "bottom" фиксированной обертки, полученное в виде строки из CSS
    var strBottomValue = ($wrapper.length > 0) ? $($wrapper).css('bottom') : 0,
        // значение "bottom", преобразованное в число
        numBottomValue = (strBottomValue > 0) ? Number(strBottomValue.replace(strBottomValue.substr(strBottomValue.length - 2), '')) : 0,
        // координата нижней границы выпадающего блока
        numElementBottomValue = ($element.length > 0) ? Number($($element).offset().top + $($element).outerHeight()) : 0,
        // координата нижней границы экрана
        numWindowBottomValue = ($parentBlock.length > 0) ? Number($($parentBlock).offset().top + $($parentBlock).outerHeight() + numBottomValue) : 0;

    if ($($wrapper) && $($wrapper).length > 0) {
        $($element).addClass(strNewClass);
        if (numElementBottomValue > numWindowBottomValue) {
            $($element).css({'top': 'auto', 'bottom': '-10px'});
        }
    }
}

// Закрытие выпадающего блока по клику вне него
// @param $element (jquery object) - выпадающий блок, который нужно закрыть
// @param strActiveClass (string) - класс для появления выпадающего блока
// @param eContext - контекст целевого события

function closeDropdownElementInFixedBlock ($element, strActiveClass, eContext) {
    'use strict';
    if (!$($element).is(eContext) && $($element).has(eContext).length === 0) {
        $($element).removeClass(strActiveClass);
    }
}

// Клонирование блока со специальностью
// @param $clonedBlock (jquery element) - клонируемый блок,
// @param strId (string) - идентификатор, добавляемый склонированному блоку

function cloneBlock($clonedBlock) {
    var $double = false,
        currentInputValue = $($clonedBlock).val(),
        $phonesFields = $('.form__input--phone');

    $($clonedBlock).unmask('');
    $double = $($clonedBlock).clone(true, true);
    $($clonedBlock).mask('+7 (000) 000 00 00');
    $($clonedBlock).val(currentInputValue).change();
    $double.val('');

    $($clonedBlock).after($double);
    $.each($($phonesFields), function(index){
        $double.attr('id', String('phone-field-' + String(index)));
    });

    $double.mask('+7 (000) 000 00 00');
}

// Закрытие и открытие элемента
// @param $element - элемент, который надо открывать и закрывать
// @param strActiveClass - класс элемента в открытом состоянии
// @param strCloseClass - класс элемента в закрытом состоянии
// @param numTimeout - время на открытие и закрытие элемента

function closeElement ($element, strActiveClass, strCloseClass, numTimeout) {
    if ($($element).hasClass(strActiveClass) == true) {
        $($element).addClass(strCloseClass);
        setTimeout(function(){
            $($element).removeClass(strActiveClass);
            $($element).removeClass(strCloseClass);
        }, numTimeout);
    }
}

