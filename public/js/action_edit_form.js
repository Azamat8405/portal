var grid;
$(function () {

	if($.fn.jqGrid)
	{
		grid = jQuery("#jqGridList");
		grid.jqGrid({
			url:'/processes/ajaxGetTovList/'+prId,
			datatype: "json",
			height:300,
			width:500,
			scroll:true,
			colNames:['Номер',
					'Товар <sup>*</sup>',
					'Код товара <sup>*</sup>',
					'Магазин <sup>*</sup>',
					'Дистрибьютор',
					'Тип акции <sup>*</sup>',
					'Размер скидки ON INVOICE (%)',
					'Процент компенсации OFF INVOICE (%)',
					'Итого скидка (%) <sup>*</sup>',
					'Старая розничная цена (руб)',
					'Новая розничная цена (руб)',
					'Старая закупочная цена (руб)',
				    'Новая закупочная цена (руб)',
				    'Дата начала скидки ON INVOICE',
				    'Дата окончания скидки ON INVOICE',
				    'Подписи, слоганы, расшифровки и пояснения к товарам в рекламе.',
				    'Пометки к товарам: Хит, Новинка, Суперцена, Выгода 0000 рублей...',
				],
			colModel:[
			   		{name:'id',width:50,align:"center",frozen:true},
			   		{name:'tovName',width:150,frozen:true},
			   		{name:'tovKod',width:150,frozen:true},
			   		{name:'shop',width:150,frozen:true},
			   		{name:'distr',width:150},
			   		{name:'type',width:150},
			   		{name:'skidka_on',width:150},
			   		{name:'skidka_off',width:150},
			   		{name:'skidka_itogo',width:150},
			   		{name:'roznica_old',width:150},
			   		{name:'roznica_new',width:150},
			   		{name:'zakup_old',width:150},
			   		{name:'zakup_new',width:150},
			   		{name:'start_date_on',width:150},
			   		{name:'end_date_on',width:150},
			   		{name:'descr',width:150,align:"center"},
			   		{name:'metka',width:150,align:"center"},
			   	],
			multiselect:false,
			pager: '#jqGridpager',
		});
		grid.jqGrid('navGrid','#jqGridpager', {edit:false,add:false,del:false,search:false,refresh:false});
	}
});