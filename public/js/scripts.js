$(function(){
	//высчитываем высоту общей панели
	var sum = 0;
	setTimeout(function(){
		var prevHeight = $('.wrapper section.header').outerHeight()+2;
		$('.content-panel-block:visible').each(function(){

			if(prevHeight > 0)
			{
				$(this).css('top', prevHeight);
			}
			tmp = $(this).outerHeight(true);
			prevHeight += tmp;
			sum += tmp;
		});
		$('.content-panel').outerHeight(sum);
	}, 100);

	//скрываем фильтры
	$(window).keydown(function(e)
	{
		if(e.keyCode == 27)//ESC
		{
			$('.hideBlock').hide();
		}
	});

	//перетаскиваем левую панель
	if($('nav .handrail > div').length > 0)
	{
		$( ".handrail > div" ).draggable({
			axis: "x",
			start: function( event, ui ){
				resizeMenu( this );
			},
			drag: function( event, ui ){
				var el = this;
				resizeMenu(el);
				setTimeout(function(){
					resizeMenu(el);
				}, 10);
			},
			stop:function()
			{
				resizeMenu(this);
			}
		});
	}

	if($('.error_dialog_messages').length > 0)
	{
		showMessage('error', '.error_dialog_messages');
	}
	if($('.success_dialog_messages').length > 0)
	{
		showMessage('success', '.success_dialog_messages');
	}

	left_menu_height();
	$(window).resize(function()
		{
			left_menu_height();
		});

	$("input").button();
	$('#tabs').tabs();

	$('ul.auth > li').click(function(e){

		if($(this).find('ul').length > 0)
		{
			e.preventDefault();
			$(this).find('ul').toggle();
		}
	});

	$('nav ul li').each(function(){
		let el = $(this).find('ul');
		if(el.length > 0)
		{
			$(this).click(function(e){
				e.preventDefault();
				el.toggle('fast');
			});

			$(this).find('ul li').click(function(e){
				e.stopPropagation();
			});
		}
	});

	$(document).click(function(e) {

		var ret = false;
		if (!$(event.target).closest('ul.auth').length && $('ul.auth ul').is(':visible'))
		{
			$('ul.auth ul').hide();
			e.stopPropagation();
			ret = true;
		}

		if (!$(event.target).closest('.hideBlock').length &&
			!$(event.target).closest('.content-panel-inputs').length &&
			!$(event.target).closest('.ui-dialog').length &&
			$('.hideBlock').is(':visible'))
		{
			$('.hideBlock').hide();
			e.stopPropagation();
			ret = true;
		}
		if(ret)
			return;
	});

	if($.fn.datepicker)
	{
	 	jQuery(function ($){
	        $.datepicker.regional['ru'] = {
	            closeText: 'Закрыть',
	            prevText: '&#x3c;Пред',
	            nextText: 'След&#x3e;',
	            currentText: 'Сегодня',
	            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
	            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
	            monthNamesShort: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
	            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
	            dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
	            dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
	            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	            weekHeader: 'Нед',
	            dateFormat: 'dd.mm.yy',
	            firstDay: 1,
	            isRTL: false,
	            showMonthAfterYear: false,
	            yearSuffix: ''
	        };
	        $.datepicker.setDefaults($.datepicker.regional['ru']);
	    });
	}
});

function left_menu_height()
{
	let padd = parseInt($('section.header').css('padding-top')) + parseInt($('section.header').css('padding-bottom'))
	let header = parseInt($('section.header').height()) + padd;
	let h = parseInt($(window).height()) - header;

	$('nav').height(h);
	$('nav .handrail > div').height(h);
}
function showMessage(type, source, message, params)
{
	if(!params)
	{
		params = {};
	}
	if(!params.width)
	{
		params.width = 650;
	}

	if($('#'+type+'_dialog_messages').length == 0)
	{
		let div = $('<div id="">');
		div.attr('id', type+'_dialog_messages');
		$('body').append(div);
	}

	$('#'+type+'_dialog_messages').dialog({
		autoOpen: true,
		width:params.width,
		maxHeight:($(window).height()-100) < 500 ? 500 : ($(window).height()-100),
		resizable:true,
		title:'Внимание!',
		position:{
			my:"top+5%",
			at:"top+5%",
			of:window
		},
		classes: {
			'ui-dialog-titlebar':type+'_dialog',
		},
		resize( event, ui )
		{
			$('#'+type+'_dialog_messages').dialog( "option", "width", ui.size.width);
		},
		open:function()
		{
			let html = '';
			if(source != '')
			{
				$(source).each(function(){
					html += $(this).html()+"<br>";
				});
			}
			if(message)
			{
				html += message;
			}
			$('#'+type+'_dialog_messages').html(html);
		},
	});
}

// для таблицы 
$(function(){

	$('#tableHeader').on('click', '#delAll', function(){

		if($(this).is(':checked'))
		{
			$('.deleteRow').prop('checked', true);
		}
		else
		{
			$('.deleteRow').prop('checked', false);
		}	
	})
});


function resizeMenu(el)
{
	var nav_w = parseInt($(el).css('left')) + 5;
	if(nav_w < 5)
	{
		$('.wrapper > nav').width(10);

		setTimeout(function(){

			tmp = parseInt($(el).css('left')) + 5;
			if(tmp < 5)
			{
				$(el).css('left', 0);
			}

		}, 100);

		$('.content').width( $(window).width() - nav_w );
		$('.content').css('margin-left', 5);

		return;
	}
	else
	{
		$('.wrapper > nav').width(nav_w);
		$('.content').width( $(window).width() - nav_w );
		$('.content').css('margin-left', nav_w);
	}

	$(window).trigger('resize');
}

function show_load(target)
{
	if(!target)
	{
		target = 'body';
	}
	$(target).append('<img class="load_img" src="/img/load75x75.gif" style="z-index:1000;width:40px;position:fixed;top:250px;left:49%;">');
}

function hide_load()
{
	$('.load_img').remove();
}

function autocompletePosition( s,e,this_)
{
	var el = $(e.target.element[0]);
	if(($(this_).outerHeight()+s.top) > $(window).height())
	{
		$(this_).css('top', el.offset().top - $(this_).outerHeight());
	}
	else
	{
		$(this_).css('top', el.offset().top + el.outerHeight());
	}
	$(this_).css('left', el.offset().left);
}