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
			   		{label:'Номер',name:'id',width:40,align:"center"},
			   		{label:'Наименование',name:'name',width:120},
			   		{label:'Начало акции',name:'stDate',width:70,align:"center"},
			   		{label:'Конец акции',name:'endDate',width:70,align:"center"},
			   		{label:'Тип',name:'type',width:80,align:"center"},
			   		{label:'Статус',name:'status',width:80,align:"center"},
			   		{label:'Автор',name:'author',width:120,align:"center"},
			   		{label:'Дата создания',name:'created_at',width:90,align:"center"},
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