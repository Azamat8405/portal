var grid;
$(function () {

	if($.fn.jqGrid)
	{
		grid = jQuery("#jqGridEdit");
		grid.jqGrid({
			url:'/processes/ajaxGetTovList/'+processId,
			datatype:'json',

			height:300,
			width:500,
			scroll:true,
			colModel:[
			   		{label:'Номер',name:'id',width:50,align:"center",frozen:true},
			   		{label:'Товар <sup>*</sup>',name:'tovName',width:150,frozen:true},
			   		{label:'Код товара <sup>*</sup>',name:'tovKod',width:150,frozen:true},
			   		{label:'Магазин <sup>*</sup>',name:'shop',width:150,frozen:true},
			   		{label:'Дистрибьютор',name:'distr',width:150},
			   		{label:'Тип акции <sup>*</sup>',name:'type',width:150},
			   		{label:'Размер скидки ON INVOICE (%)',name:'skidka_on',width:150},
			   		{label:'Процент компенсации OFF INVOICE (%)',name:'skidka_off',width:150},
			   		{label:'Итого скидка (%) <sup>*</sup>',name:'skidka_itogo',width:150},
			   		{label:'Старая розничная цена (руб)',name:'roznica_old',width:150},
			   		{label:'Новая розничная цена (руб)',name:'roznica_new',width:150},
			   		{label:'Старая закупочная цена (руб)',name:'zakup_old',width:150},
			   		{label:'Новая закупочная цена (руб)',name:'zakup_new',width:150},
			   		{label:'Дата начала скидки ON INVOICE',name:'start_date_on',width:150},
			   		{label:'Дата окончания скидки ON INVOICE',name:'end_date_on',width:150},
			   		{label:'Подписи, слоганы, расшифровки и пояснения к товарам в рекламе.',name:'descr',width:150,align:"center"},
			   		{label:'Пометки к товарам: Хит, Новинка, Суперцена, Выгода 0000 рублей...',name:'metka',width:150,align:"center"},
				],
			multiselect:false,
			pager: '#jqGridEditPager',
		});
		grid.jqGrid('navGrid','#jqGridEditPager', {edit:false,add:false,del:false,search:false,refresh:false});
	}
});