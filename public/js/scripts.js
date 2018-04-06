$(function(){

	$(window).keydown(function(e)
	{
		if(e.keyCode == 27)//ESC
		{
			$('.hideBlock').hide();
		}
	});

	if($('nav .handrail > div').length > 0)
	{
		$( ".handrail > div" ).draggable({
			axis: "x",
			drag: function( event, ui ) {
				resizeMenu(this);
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
	$(window).resize(left_menu_height);

	$("input").button();
	$('#tabs').tabs();

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

		if ($(event.target).closest('ul.auth').length)
			return;

		$('ul.auth ul').hide();
		e.stopPropagation();
	});

	$('ul.auth > li').click(function(e){

		if($(this).find('ul').length > 0)
		{
			e.preventDefault();
			$(this).find('ul').toggle();
		}
	});

	if($.fn.datepicker)
	{
	 	jQuery(function ($) {
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
function showMessage(type, source, message)
{
	if($('#'+type+'_dialog_messages').length == 0)
	{
		let div = $('<div id="">');
		div.attr('id', type+'_dialog_messages');
		$('body').append(div);
	}

	$('#'+type+'_dialog_messages').dialog({
		autoOpen: true,
		width:650,
		title:'Внимание!',
		classes: {
			'ui-dialog-titlebar':type+'_dialog',
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
		}
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
	var nav_w = parseInt($(el).css('left')) + 9;
	if(nav_w < 10)
	{
		$('.wrapper > nav').width(10);

		setTimeout(function(){

			tmp = parseInt($(el).css('left')) + 9;
			if(tmp < 10)
			{
				$(el).css('left', 0);
			}

		}, 100);

		$('.content').width( $(window).width() - nav_w );
		$('.content').css('margin-left', 12);

		return;
	}
	else
	{
		$('.wrapper > nav').width(nav_w);
		$('.content').width( $(window).width() - nav_w );
		$('.content').css('margin-left', nav_w);
	}
}