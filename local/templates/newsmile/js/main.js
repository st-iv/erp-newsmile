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
	}
	$('.notif_content .notif_tab').on('click', function(){
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
	});

	$('.notif_item .notif_close').on('click', function(){
		$(this).closest('.notif_item').remove();
	});	

	$(".notif_items, .search_res_cont").mCustomScrollbar({
		autoHideScrollbar: false
	});

	$('.notif_bell, .notif_content_close').on('click', function(){
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
	});

	$('.search_sbmt').on('click', function(){
		var $menu = $('.search_content');

		$('body').toggleClass('searchOpen');

		if ($('body').hasClass('searchOpen')){
			$(document).on('mousedown.search', function(e){
			    if(!$(e.target).hasClass('search_sbmt') && !$(e.target).hasClass('search_content') && !$menu.has(e.target).length){
					$('body').removeClass('searchOpen');
			    }
			});			
		} else {
			$(document).off('mousedown.search');
		}
	});

    $.widget( "custom.iconselectmenu", $.ui.selectmenu, {
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

	$("#speс").selectmenu();

	function prepareTime(x){
		var hours = parseInt(x / 4),
			minutes = (x - hours * 4) * 15; 

		if (hours.toString().length === 1){
			hours = '0' + hours;
		}
		if (minutes === 0){
			minutes = '00';
		}
		return hours + ':' + minutes;
	}	
	$( "#time-range" ).slider({
		range: true,
		min: 36,
		max: 72,
		values: [36,72],
		slide: function(event,ui) {
			$("#time-range_from span").text(prepareTime(ui.values[0]));
			$("#time-range_to span").text(prepareTime(ui.values[1]));
		}
	});
	$("#time-range_from span").text(prepareTime($("#time-range").slider( "values",0)));
	$("#time-range_to span").text(prepareTime($("#time-range").slider( "values",1)));

	$( "#left-calendar" ).customCalendar({
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
	});
});