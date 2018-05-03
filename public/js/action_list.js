var grid;
$(function () {
	if($.fn.jqGrid)
	{
		grid = jQuery("#jqGridList");
		grid.jqGrid({
			url:'/processes/ajaxList',
			datatype: "json",
			height:300,
			colModel:[
			   		{label:'Номер', name:'id',width:40,align:"center", search:false},
			   		{label:'Наименование',name:'title',width:120, search:true},
			   		{
			   			label:'Начало акции',
			   			name:'start_date',
			   			width:70,
			   			align:"center",
	   					searchoptions:{
	   						dataInit:function(elem){
			   					$(elem).datepicker({
 									onSelect: function () {
			   							grid[0].triggerToolbar();
			   						}
			   					});
	   						}
	   					}
				   	},
			   		{
			   			label:'Конец акции',
			   			name:'end_date',
			   			width:70,
			   			align:"center",
	   					searchoptions:{
	   						dataInit:function(elem){
			   					$(elem).datepicker({
 									onSelect: function () {
			   							grid[0].triggerToolbar();
			   						}
			   					});
	   						}
	   					}
			   		},
			   		{label:'Тип',name:'type',width:80,align:"center"},
			   		{label:'Статус',name:'status',width:80,align:"center"},
			   		{label:'Автор',name:'author',width:120,align:"center"},
			   		{
			   			label:'Дата создания',
			   			name:'created_at',
			   			width:90,
			   			align:"center",
			   			firstsortorder:'desc',
	   					searchoptions:{
	   						dataInit:function(elem){
			   					$(elem).datepicker({
 									onSelect: function () {
			   							grid[0].triggerToolbar();
			   						}
			   					});
	   						}
	   					}
	   				},
			   	],
			multiselect:false,
			sortorder:'desc',
			pager: '#jqGridpager',
			rowList: [10, 20, 30, 50],
			ondblClickRow: function(rowid)
			{
				if(!rowid)
					return;
				window.location.href="/processes/edit/"+rowid;
			}
		});
		grid.jqGrid('navGrid','#jqGridpager', {edit:false,add:false,del:false,search:false,refresh:false});
		grid.jqGrid('filterToolbar', {
				gridModel:true,
				gridNames:true,
				formtype:"vertical",
				enableSearch:true,
				enableClear:false,
				searchOnEnter:true,
				autosearch:true,
				multipleSearch:false,
			});
	}
});