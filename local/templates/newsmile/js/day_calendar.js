;( function( $, window, document, undefined ) {
	"use strict";
		var pluginName = "dayCalendar",
			defaults = {
				propertyName: "value"
			};
		function Plugin ( element, options ) {
			this.element = element;
			this.settings = $.extend( {}, defaults, options );
			this._defaults = defaults;
			this._months = {
				0: 'января',
				1: 'февраля',
				2: 'марта',
				3: 'апреля',
				4: 'мая',
				5: 'июня',
				6: 'июля',
				7: 'августа',
				8: 'сентября',
				9: 'октября',
				10: 'ноября',
				11: 'декабря'
			}
			this._days = {
				1: 'Понедельник',
				2: 'Вторник',
				3: 'Среда',
				4: 'Четверг',
				5: 'Пятница',
				6: 'Суббота',
				7: 'Воскресенье'
			}	
			this._svgPaid = '<svg class="ptnt_status_icon" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34.669334 25.1348" height="25.1348" width="34.669334" xml:space="preserve" id="svg2" version="1.1"><metadata id="metadata8"><rdf:RDF><cc:Work rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/></cc:Work></rdf:RDF></metadata><defs id="defs6"><clipPath id="clipPath18" clipPathUnits="userSpaceOnUse"><path id="path16" d="M 0,18.851 H 26.002 V 0 H 0 Z"/></clipPath></defs><g transform="matrix(1.3333333,0,0,-1.3333333,0,25.1348)" id="g10"><g id="g12"><g clip-path="url(#clipPath18)" id="g14"><g transform="translate(2.0001,10.1293)" id="g20"><path id="path22" style="fill:none;stroke:#123321;stroke-width:4;stroke-linecap:round;stroke-linejoin:miter;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" d="M 0,0 9.591,-7.399 22.002,6.722"/></g></g></g></g></svg>';
			this._svgDecl = '<svg class="ptnt_status_icon" height="4" width="9"><line x1="0" y1="0" x2="9" y2="0" style="stroke:#123321;stroke-width:2" /></svg>';								
			this._svgMenuArrow = '<svg version="1.1" id="svg2" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 13.2 22.1" style="enable-background:new 0 0 13.2 22.1;" xml:space="preserve"><style type="text/css">.st0{clip-path:url(#SVGID_2_);}.st1{fill:none;stroke:#00D7D7;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}</style><g id="g10" transform="matrix(1.3333333,0,0,-1.3333333,0,13.16664)"><g id="g12"><g><defs><rect id="SVGID_1_" x="-1.6" y="-9.4" width="13.2" height="22.1"/></defs><clipPath id="SVGID_2_"><use xlink:href="#SVGID_1_" style="overflow:visible;"/></clipPath><g id="g14" class="st0"><g id="g20" transform="translate(1.5,8.18)"><path id="path22" class="st1" d="M0.2-13.4l6.7,6.7L0,0.2"/></g></g></g></g></g></svg>';
			this._name = pluginName;
			this._intervalsArray = [];
			this._freeDctrsIntrvls = {};
			this._doctorsObj = {};
			this._patientsObj = {};
			this._popupOpen = false;
			this.init();
		}

		// Avoid Plugin.prototype conflicts
		$.extend( Plugin.prototype, {
			init: function() {
				var $cont = $('<div class="dayCalendar_cont"></div>'),
					$header = $('<div class="dayCalendar_header">'+
						'<span></span>'+
					'</div>'),
					$body = $('<div class="dayCalendar_body"></div>'),
					$leftTimeline = $('<div class="dayCalendar_leftTl"></div>'),
					$rightTimeline = $('<div class="dayCalendar_rightTl"></div>'),
					$popup = $('<div class="dayCalendar_popup">' +
						'<div class="dClndr_popup_card">' +
							'<div class="dpopup_card_statuses"></div>' +							
							'<div class="dClndr_popup_info">' +
								'<div class="dClndr_pinfo_name"></div>' +
								'<div class="dClndr_pinfo_number"></div>' +	
								'<div class="dClndr_pinfo_phone"></div>' +	
								'<div class="dClndr_pinfo_time"></div>' +							
							'</div>' +
						'</div>' +
						'<div class="dClndr_parrow"></div>' +	
					'</div>'),
					$popupMenu = $('<div class="dClndr_popup_menu">' +
						'<ul class="dClndr_pmenu1">' +
							'<li class="dClndr_phasmenu">Статус пациента' +
								'<ul class="dClndr_psubmenu">' +
									'<li>Опаздывает</li>' +
									'<li>Пришёл</li>' +
									'<li>Ожидает приёма</li>' +
								'</ul>' +
							'</li>' +
							'<li class="dClndr_phasmenu">Оповестить' +
								'<ul class="dClndr_psubmenu">' +
									'<li>Опаздывает</li>' +
									'<li>Пришёл</li>' +
									'<li>Ожидает приёма</li>' +
								'</ul>' +
							'</li>' +
							'<li class="dClndr_phasmenu">Изменить врача' +
								'<ul class="dClndr_psubmenu">' +
									'<li>Опаздывает</li>' +
									'<li>Пришёл</li>' +
									'<li>Ожидает приёма</li>' +
								'</ul>' +
							'</li>' +
							'<li>Добавить в лист ожидания</li>' +
							'<li>Разделить интервал</li>' +
							'<li>Отменить приём</li>' +
							'<li>Перенести приём</li>' +
						'</ul>' +
						'<ul class="dClndr_pmenu2">' +
							'<li class="dClndr_phasmenu">Статус пациента' +
								'<ul class="dClndr_psubmenu">' +
									'<li>Опаздывает</li>' +
									'<li>Пришёл</li>' +
									'<li>Ожидает приёма</li>' +
								'</ul>' +
							'</li>' +
							'<li class="dClndr_phasmenu">Оповестить' +
								'<ul class="dClndr_psubmenu">' +
									'<li>Опаздывает</li>' +
									'<li>Пришёл</li>' +
									'<li>Ожидает приёма</li>' +
								'</ul>' +
							'</li>' +
							'<li class="dClndr_phasmenu">Изменить врача' +
								'<ul class="dClndr_psubmenu">' +
									'<li>Опаздывает</li>' +
									'<li>Пришёл</li>' +
									'<li>Ожидает приёма</li>' +
								'</ul>' +
							'</li>' +
							'<li>Разделить интервал</li>' +							
						'</ul>' +
					'</div>'),
					$curTime = $('<div class="dayCalendar_curTime"></div>'),
					curDate = moment(this.settings.curDate),
					dayName = this._days[curDate.day()],
					dayNumber = curDate.date(),
					monthName = this._months[curDate.month()],
					startTime = moment(this.settings.curDate + ' ' + this.settings.startTime),
					endTime = moment(this.settings.curDate + ' ' + this.settings.endTime),
					self = this,
					i;

				// заголовок расписания
				$header.children('span').text(dayName + ' - ' + dayNumber + ' ' + monthName);
				
				$header.prependTo($cont);

				//текущее время
				$curTime.prependTo($body);
				$cont.prependTo($( this.element ));
				
				while (endTime.diff(startTime, 'minutes') > 0) {
					this._intervalsArray.push(startTime.format("HH:mm"));

					startTime.add(this.settings.timeStep, 'minutes');
				}

				this.settings.cData.forEach(function(room) {
					room.patients.forEach(function(patient) {
						self.appendTimeInterval(patient.timeFrom);
						self.appendTimeInterval(patient.timeTo);
					});
				});

				this.settings.cData.forEach(function(room) {
					if (room.freeDoctors){
						room.freeDoctors.forEach(function(freeDoctor) {
							room.patients.forEach(function(patient) {
								if (patient.doctorId === freeDoctor.id){
									if ((_.indexOf(self._intervalsArray, patient.timeTo) - _.indexOf(self._intervalsArray, patient.timeFrom)) === 1){
										self._freeDctrsIntrvls[patient.timeFrom] = true;
									}
								}
							});
						});
					}
				});

				this.settings.cData.forEach(function(room) {
					var $bodyColumn = $('<div class="dayCalendar_column"></div>'),
						doctor1style = ' style = "background-color: #' + room.doctors[0].color + ';"',
						doctor2style,
						intervalHeight,
						intervalStyle,
						intervalData,
						intervalClass,
						intrvlStatusHtml,
						freeIntrvlText,
						ifIntrvlEmpty,
						checkIntrvlRslt,
						i,
						j,
						z; 

					if (room.doctors[1]){
						doctor2style = ' style = "background-color: #' + room.doctors[1].color + ';"';
					}

					room.doctors.forEach(function(doctor) {
						self._doctorsObj[doctor.id] = {};
						self._doctorsObj[doctor.id]['color'] = '#' + doctor.color;
						self._doctorsObj[doctor.id]['name'] = '#' + doctor.name;
					});
					if (room.freeDoctors){
						room.freeDoctors.forEach(function(doctor) {
							self._doctorsObj[doctor.id] = {};
							self._doctorsObj[doctor.id]['color'] = '#' + doctor.color;
							self._doctorsObj[doctor.id]['name'] = doctor.name;
							self._doctorsObj[doctor.id]['ifFree'] = true;
						});
					}
					$bodyColumn.append($('<div class="dayCalendar_roomName">' + room.name + '</div>'));

					if (room.doctors.length === 1){
						$bodyColumn.append($('<div class="dayCalendar_doctor"' + doctor1style + '>' + room.doctors[0].name + '</div><div class="dayCalendar_doctor emptyD"></div>'));
					} else if ((room.doctors.length === 2) && (room.doctors[0].id === room.doctors[1].id)){
						$bodyColumn.append($('<div class="dayCalendar_doctor sameD"' + doctor1style + '>' + room.doctors[0].name + '</div><div class="dayCalendar_doctor"' + doctor1style + '>' + room.doctors[0].name + '</div>'));
					} else if ((room.doctors.length === 2) && (room.doctors[0].id != room.doctors[1].id)){
						 $bodyColumn.append($('<div class="dayCalendar_doctor"' + doctor1style + '>' + room.doctors[0].name + '</div><div class="dayCalendar_doctor"' + doctor2style + '>' + room.doctors[1].name + '</div>'));
					}

					j = 0;
					i = 0;
					while (j <= room.patients.length - 1){
						if (room.patients[j].timeFrom === self._intervalsArray[i]) {
							intervalHeight = 1;
							z = i + 1;
							while (z <= self._intervalsArray.length - 1){
								if(room.patients[j].timeTo === self._intervalsArray[z]){
									if (self._freeDctrsIntrvls[room.patients[j].timeFrom] && intervalHeight === 1){
										intervalHeight = 2;
									}

									intervalStyle = ' style = "height: ' + (intervalHeight * 22 + (intervalHeight-1) * 2) + 'px;' +
										'background-color: '+ self.lightenColor(self._doctorsObj[room.patients[j].doctorId].color, 30) +';'+
										'color: '+ self.darkenColor(self._doctorsObj[room.patients[j].doctorId].color, 50) +';'+
										'border-color: '+ self.lightenColor(self._doctorsObj[room.patients[j].doctorId].color, 13) +';'+
										'"';

									intervalClass = '';
									intrvlStatusHtml = '';
									intervalData = '';
									room.patients[j].info.statuses.forEach(function(status) {
										intervalClass += ' ptnt_' + status;
									});
									if (_.indexOf(room.patients[j].info.statuses, 'paid') != -1){
										intrvlStatusHtml = self._svgPaid;
										intrvlStatusHtml = intrvlStatusHtml.replace(/#123321/, self.darkenColor(self._doctorsObj[room.patients[j].doctorId].color, 50));
									} else if (_.indexOf(room.patients[j].info.statuses, 'decl') != -1){
										intrvlStatusHtml = self._svgDecl;
										intrvlStatusHtml = intrvlStatusHtml.replace(/#123321/, self.darkenColor(self._doctorsObj[room.patients[j].doctorId].color, 50));
									}

									if (self._doctorsObj[room.patients[j].doctorId].ifFree){
										$bodyColumn.append($('<div class="dayCalendar_interval"'+ intervalStyle +'><span>' + room.patients[j].name + '</span><span class="freedoctor_intrvl">Врач - ' + self._doctorsObj[room.patients[j].doctorId].name + '</span></div>'));
									} else {
										intervalData = ' data-cardid ="' + room.patients[j].info.cardNumber + '"';
										self._patientsObj[room.patients[j].info.cardNumber] = room.patients[j];
										$bodyColumn.append($('<div class="dayCalendar_interval has-popover'+ intervalClass +'"'+ intervalStyle + intervalData + '><span>'+ intrvlStatusHtml + room.patients[j].name + '</span></div>'));
									}
									
									i = z;
									break;
								} else if (self._freeDctrsIntrvls[self._intervalsArray[z]]){
									intervalHeight += 2;
									z++;
								} else {
									intervalHeight++;
									z++;
								}
							}
							if (j < room.patients.length - 1){
								j++;
							}	
						} else {
							intervalStyle = ' style = "';
							ifIntrvlEmpty = true;
							freeIntrvlText = '';

							checkIntrvlRslt = self.checkTimeInterval(room.doctors, self._intervalsArray[i]);
							intervalStyle += checkIntrvlRslt.intervalStyle;
							ifIntrvlEmpty = checkIntrvlRslt.ifIntrvlEmpty;

							if (room.freeDoctors && ifIntrvlEmpty){
								checkIntrvlRslt = self.checkTimeInterval(room.freeDoctors, self._intervalsArray[i]);
								intervalStyle += checkIntrvlRslt.intervalStyle;
								ifIntrvlEmpty = checkIntrvlRslt.ifIntrvlEmpty;
								freeIntrvlText = checkIntrvlRslt.freeIntrvlText;
							}

							if (self._freeDctrsIntrvls[self._intervalsArray[i]]){
								intervalStyle += 'height: 46px;';
							}

							intervalStyle += '"';

							if (!ifIntrvlEmpty){
								$bodyColumn.append($('<div class="dayCalendar_interval resrvdI"'+ intervalStyle +'>' + freeIntrvlText + '</div>'));	
							} else {
								$bodyColumn.append($('<div class="dayCalendar_interval emptyI"'+ intervalStyle +'></div>'));	
							}
							
							if (i === (self._intervalsArray.length - 1))
								break;	
							i++;
						}
					}	
					$bodyColumn.appendTo($body);
				});

				i = 0; 
				while (i <= this._intervalsArray.length - 1){
					var timeItemStyle = '',
						timeItemClass = '';

					if (this._freeDctrsIntrvls[this._intervalsArray[i]]){
						timeItemStyle += ' style = "height: 46px;"';
					}
					if (this._intervalsArray[i].slice(-1) === '0'){
						if (this._intervalsArray[i+1]){
							if (this._intervalsArray[i+1].slice(-1) === '5'){
								timeItemClass = ' littleTI';
							}
						}
					} else if (this._intervalsArray[i].slice(-1) === '5'){
						timeItemClass = ' littleTI';
					}

					$leftTimeline.append($('<div class="dayCalendar_timeItem'+ timeItemClass +'"'+ timeItemStyle +'><span>'+ this._intervalsArray[i] +'</span></div>'));
					$rightTimeline.append($('<div class="dayCalendar_timeItem'+ timeItemClass +'"'+ timeItemStyle +'><span>'+ this._intervalsArray[i] +'</span></div>'));
					
					i++;
				};

				$leftTimeline.appendTo($body);
				$rightTimeline.appendTo($body);				
				$body.appendTo($cont);

				i = 0;
				var curTimeOffset = 81;
				while (i <= this._intervalsArray.length - 1){
					
					if (this._intervalsArray[i+1]){
						if(moment('2010-10-10 ' + this.settings.curTime).isBetween('2010-10-10 ' + this._intervalsArray[i], '2010-10-10 ' + this._intervalsArray[i+1], null, '[]')){
							this.locateCurTime(this._intervalsArray[i], this._intervalsArray[i+1], this.settings.curTime, curTimeOffset);
							break;
						} else {
							curTimeOffset += this._freeDctrsIntrvls[this._intervalsArray[i]] ? 46 : 24;
						}
					} else {
						if(moment('2010-10-10 ' + this.settings.curTime).isBetween('2010-10-10 ' + this._intervalsArray[i], '2010-10-10 ' + this.settings.endTime, null, '[]')){
							this.locateCurTime(this._intervalsArray[i], this.settings.endTime, this.settings.curTime, curTimeOffset);
							break;
						} else {
							curTimeOffset += this._freeDctrsIntrvls[this._intervalsArray[i]] ? 46 : 24;
						}
					}					

					i++;
				}

				$(this.element).find('.dayCalendar_interval.has-popover').each(function(){
					var $interval = $(this),
						cardId = $interval.data('cardid'),
						$intervalPopup = $popup.clone();

					$interval.append($intervalPopup);	
					$intervalPopup.find('.dpopup_card_statuses, .dClndr_pinfo_name, .dClndr_pinfo_number, .dClndr_pinfo_phone, .dClndr_pinfo_time').html('');

					self._patientsObj[cardId].info.statuses.forEach(function(status) {
						switch(status) {
							case 'perv':
								$intervalPopup.find('.dpopup_card_statuses').append($('<div class="ptnt_perv">Первичный</div>'));
								break;

							case 'paid':
								$intervalPopup.find('.dpopup_card_statuses').append($('<div class="ptnt_paid">' + self._svgPaid + 'Пациент рассчитан</div>'));
								break;

							case 'decl':
								$intervalPopup.find('.dpopup_card_statuses').append($('<div class="ptnt_decl">' + self._svgDecl + 'Пациент не пришёл</div>'));
								break;

							default:
						}
					});

					$intervalPopup.find('.dClndr_pinfo_name').html($('<div><span>' + self._patientsObj[cardId].info.fullName + '</span> - ' + self.getAgeString(self._patientsObj[cardId].info.age) + '</div>'));
					$intervalPopup.find('.dClndr_pinfo_number').html($('<div>Карта ' + self._patientsObj[cardId].info.cardNumber + '</div><span></span>'));
					$intervalPopup.find('.dClndr_pinfo_phone').html($('<div>' + self._patientsObj[cardId].info.phone + '</div>'));
					$intervalPopup.find('.dClndr_pinfo_time').html('<span>' + self._patientsObj[cardId].timeFrom + ' - ' + self._patientsObj[cardId].timeTo + '</span><span>' + self.getTimeString(self._patientsObj[cardId].timeFrom, self._patientsObj[cardId].timeTo) + '</span>');

					$intervalPopup.prepend($popupMenu.clone());
					$intervalPopup.find('.dClndr_phasmenu')
						.append($(self._svgMenuArrow))
						.on('mouseenter', function(){
							$interval.addClass('dClndr_submemu_act');
						})
						.on('mouseleave', function(){
							$interval.removeClass('dClndr_submemu_act');
						});

					var	popper = new Popper($interval, $intervalPopup,{
						placement: 'bottom-start',
						onUpdate: function(data){
							console.log(data);
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
			                    enabled: true/*,
			                    offset: '50%,0'*/
			                }
						}
					});

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
								//console.log(popper);
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
				});
			},
			checkTimeInterval: function(doctors, timeInterval) {
				var self = this,
					ifIntrvlEmpty = true,
					intervalStyle = '',
					freeIntrvlText ='',
					q = 0;

				while (q <= doctors.length - 1){
					doctors[q].workTime.forEach(function(workTime) {
						if (moment('2010-10-10 ' + timeInterval).isBetween('2010-10-10 ' + workTime.startTime, '2010-10-10 ' + workTime.endTime, null, '[)')){
							intervalStyle = 'background-color: '+ self.lightenColor('#' + doctors[q].color, 42) + ';';
							ifIntrvlEmpty = false;	
							if (self._doctorsObj[doctors[q].id].ifFree){
								freeIntrvlText = '<span class="freedoctor_intrvl">Врач - ' + self._doctorsObj[doctors[q].id].name + '</span>';
								intervalStyle += 'color: '+ self.darkenColor(self._doctorsObj[doctors[q].id].color, 50) +';';
							}
						}
					});
					if (!ifIntrvlEmpty)
						break;
					q++;
				};

				return {
					'ifIntrvlEmpty' : ifIntrvlEmpty,
					'intervalStyle' : intervalStyle,
					'freeIntrvlText' : freeIntrvlText
				}
			},			
			appendTimeInterval: function(timeInterval) {
				if (_.indexOf(this._intervalsArray, timeInterval) === -1){
					var helperMoment = moment(this.settings.curDate + ' ' + timeInterval),
						indexAppendAfter;

					helperMoment.subtract(this.settings.mintimeStep, 'minutes');
					indexAppendAfter = _.indexOf(this._intervalsArray, helperMoment.format("HH:mm"));

					if (indexAppendAfter){
						indexAppendAfter += 1;
						this._intervalsArray.splice(indexAppendAfter, 0, timeInterval);
					}
				}
			},
			lightenColor: function(initColor, value){
				var color = tinycolor(initColor),
					coef = 1 - Math.pow((color.getBrightness() / 255), 3);

				return color.lighten(value * coef).toString();
			},
			darkenColor: function(initColor, value){
				var color = tinycolor(initColor),
					coef = color.getBrightness() / 255;

				return color.darken(value * coef).toString();
			},
			getProperNumString: function(x, text_variations){
		        var n = Math.abs(x) % 100,
		        	n1 = n % 10;

		        if (n > 10 && n < 20) { return x + text_variations[2]; }
		        if (n1 > 1 && n1 < 5) { return x + text_variations[1]; }
		        if (n1 == 1) { return x + text_variations[0]; }
		        return x + text_variations[2];				
			},
			getAgeString: function(age){
		        return this.getProperNumString(age, [' год', ' года', ' лет']);				
			},
			getTimeString: function(timeFrom, timeTo){
				var helperMoment = moment.duration(moment('2010-10-10 ' + timeTo).diff(moment('2010-10-10 ' + timeFrom))),
					hours = (helperMoment.hours() > 0) ? helperMoment.hours() : '',
					minutes = (helperMoment.minutes() > 0) ? helperMoment.minutes() : '';

				if (hours){
					hours = this.getProperNumString(hours, [' час', ' часа', ' часов']) + ' ';		
				}
				if (minutes){
					minutes = minutes + ' минут ';		
				}
				return hours + minutes;     			
			},
			locateCurTime: function(timeFrom, timeTo, curTime, curOffset){
     			var intervalHeight = this._freeDctrsIntrvls[timeFrom] ? 46 : 24,
     				innerOffset = moment('2010-10-10 ' + curTime).diff(moment('2010-10-10 ' + timeFrom))/moment('2010-10-10 ' + timeTo).diff(moment('2010-10-10 ' + timeFrom));

     			$(this.element).find('.dayCalendar_curTime').css({top: curOffset + parseInt(intervalHeight * innerOffset) + 'px'});
			}
		});
		$.fn[ pluginName ] = function(options) {
			return this.each(function(){
				if (!$.data(this,"plugin_" + pluginName)){
					$.data(this,"plugin_" + pluginName, new Plugin(this,options));
				}
			});
		};
})(jQuery,window,document);