$(document).ready(function(){
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