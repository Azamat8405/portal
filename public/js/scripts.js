$(function(){

	if($.fn.handsontable && $('#table_data').length > 0)
	{
		var data = [
			["", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda"],
			["2017", 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2019", 30, 15, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13]
		];

		var container = document.getElementById('table_data');
		var hot = new Handsontable(container, {
			data:data,
			colWidths: [50,50,50],
			rowHeaders:true,
			colHeaders:true,

			allowRemoveColumn:false,
			allowRemoveRow:false,
			allowInsertColumn:false,
			allowInsertRow:false,

			minSpareRows:20,
			manualRowMove: true,
			manualColumnMove: true,
			manualRowResize: true,
			manualColumnResize: true,
			filters:true,
			stretchH: 'all',
		    contextMenu: true,
			height:500,
			width:function(){
				return $('#tabs-1').width() - 20;
			},
			afterChange:function (change, source)
			{
				if (source === 'loadData')
					return;
			}
		});
		hot.updateSettings({
	    	cells: function (row, col, prop) {
				var cellProperties = {};


	      		// if (hot.getSourceData()[row][prop] === 'Nissan')
	      		if(row == 2 && col == 2)
	      		{
	        		cellProperties.readOnly = true;
				}
				return cellProperties;
			}
		});
	}

	left_menu_height();
	$(window).resize(left_menu_height);

	$(".content-panel input").button();
	$("#tabs input").button();
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
});

function left_menu_height()
{
	let padd = parseInt($('section.header').css('padding-top')) + parseInt($('section.header').css('padding-bottom'))
	let header = parseInt($('section.header').height()) + padd;

	$('nav').height(parseInt($(window).height()) - header);
}