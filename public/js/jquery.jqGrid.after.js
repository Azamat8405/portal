/*Подключаем после инициализации таблицы модуля jqGrid */
$(function () {

	if($.fn.jqGrid)
	{
		setTimeout(function(){
			resizeHeightjqGrid(grid);
			resizeWidthjqGrid(grid);
		}, 100);

		$(window).resize(function(){
			resizeHeightjqGrid(grid);
			resizeWidthjqGrid(grid);
		});
	}
});

function resizeHeightjqGrid(grid)
{
	var tmp = $('.content-panel').outerHeight()
		+$('.wrapper section.header').outerHeight()
		+$('.ui-jqgrid-htable').outerHeight()
		+ ($('.ui-jqgrid-pager').length > 0 ? $('.ui-jqgrid-pager').outerHeight() : 0) 
		+10;
	var gridHeight = $(window).height() - tmp;
	grid.jqGrid("setGridHeight", gridHeight);
}

function resizeWidthjqGrid(grid)
{
	var w = grid.jqGrid("getGridParam", 'width')
	var w2 = $('.content_body').width();
	if(w < w2)
	{
		grid.jqGrid("setGridWidth", w2);
	}
}