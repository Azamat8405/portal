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
	grid.jqGrid("setGridWidth", $('.content_body').width()-5);
}
function setFrozenHeightTd()
{
	// setTimeout(function(){
		if($('#'+grid.attr('id')+'_frozen tr.jqgrow').length == 0)
		{
			setFrozenHeightTd();
			return;
		}
		$('#'+grid.attr('id')+'_frozen tr.jqgrow').each(function(index){
			var h = grid.find('tr.jqgrow:eq('+index+') td:eq(0)').outerHeight();
			$(this).find('td').each(function(index2){

				$(this).css('height',h);
			});
			$(this).css('height',h);
		});

		$('.frozen-div.ui-jqgrid-hdiv').height($('.ui-jqgrid-hdiv').height());
	// }, 20);
}