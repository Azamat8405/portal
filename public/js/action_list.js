var grid;
$(function () {
	
	if($.fn.jqGrid)
	{
		grid = jQuery("#jqGridList");
		grid.jqGrid({
			url:'/processes/ajaxList',
			datatype: "json",
			height:300,
			colNames:['Номер','Наименование','Начало акции','Конец акции','Тип'],
			colModel:[
			   		{name:'id',width:50,align:"center"},
			   		{name:'name',width:90},
			   		{name:'stDate',width:100,align:"center"},
			   		{name:'endDate',width:80,align:"center"},
			   		{name:'type',width:80,align:"center"},
			   	],
			multiselect:false,
			pager: '#jqGridpager',
			ondblClickRow: function(rowid)
			{
				if(!rowid)
					return;
				window.location.href="/processes/edit/"+rowid;
			}
		});
		grid.jqGrid('navGrid','#jqGridpager', {edit:false,add:false,del:false,search:false,refresh:false});
	}
});