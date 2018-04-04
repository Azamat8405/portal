$(function(){

	if($('.error_dialog_messages').length > 0)
	{
		showMessage('error', '.error_dialog_messages');
	}
	if($('.success_dialog_messages').length > 0)
	{
		showMessage('success', '.success_dialog_messages');
	}

	if($('nav .handrail > div').length > 0)
	{
		$('nav .handrail > div').click(function(){
			var el = this;
			if($(el).parents('nav').width() > 0)
			{

				$('.content-panel').css('width', '99%');
				$(".content").css('margin-left', 0);
				$(".content").css('width', '100%');

				$(el).parents('nav').width(0);

				// $(".content").animate({
				//     'margin-left': 0,
				//     'width': '100%',
				//    }, 100, function()  {

				// 	$(el).parents('nav').width(0);
				//   });
    		}
    		else
    		{
				$(el).parents('nav').width('15%');
				$('.content').css('margin-left', '15%');
				$('.content').css('width', '85%');
				$('.content-panel').css('width', '84%');
    		}

			$(window).trigger('resize');

		})
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