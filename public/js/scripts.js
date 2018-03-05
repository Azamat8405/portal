$(function(){

	if($.fn.handsontable && $('#table_data').length > 0)
	{
		// var data = [
		// 	["", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda"],
		// 	["2017", "<div>sfsdf</div>", 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
		// 	["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
		// 	["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
		// 	["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
		// 	["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
		// 	["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
		// 	["2019", 30, 15, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13]
		// ];

		// var container = document.getElementById('table_data');
		// var hot = new Handsontable(container, {
		// 	data:data,
		// 	colWidths: [50,50,50],
		// 	rowHeaders:true,
		// 	colHeaders:true,

		// 	sortIndicator: true,
		// 	columnSorting: {
		// 		column: 2
		// 	},

		// 	allowRemoveColumn:false,
		// 	allowRemoveRow:false,
		// 	allowInsertColumn:false,
		// 	allowInsertRow:false,

		// 	columns: [{
		// 		type: 'autocomplete',
		// 		allowHtml: true,
		// 		source: ['<b>foo</b>', '<b>bar</b>']
		// 	}],

		// 	minSpareRows:20,
		// 	manualRowMove: true,
		// 	manualColumnMove: true,
		// 	manualRowResize: true,
		// 	manualColumnResize: true,
		// 	filters:true,
		// 	stretchH: 'all',
		//     contextMenu: true,
		// 	height:500,
		// 	width:function(){
		// 		return $('#tabs-1').width() - 20;
		// 	},
		// 	afterChange:function (change, source)
		// 	{
		// 		if (source === 'loadData')
		// 			return;
		// 	}
		// });
		// hot.updateSettings({
	 //    	cells: function (row, col, prop) {
		// 		var cellProperties = {};

	 //      		if(row == 2 && col == 2)
	 //      		{
	 //        		cellProperties.readOnly = true;
		// 		}
		// 		return cellProperties;
		// 	}
		// });


		// $('.htCore td').click(function(){
		// 	console.log('55555');
		// });

		// $('.htCore tr').each(function(i){

		// 	if(i == 0)
		// 		return;

		// 	$exist = false;
		// 	$(this).find('td').each(function() {

		// 		if($(this).html() == 123456789)
		// 		{
		// 			$exist = true;
		// 		}
		// 	});

		// 	if(!$exist)
		// 	{
		// 		$(this).hide();
		// 	}
		// });

		// $('.htCore td').change(function()
		// {
		// 	console.log('777');
		// });

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

	$('nav').height(parseInt($(window).height()) - header);
}