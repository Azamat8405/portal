var grid;
var dopData = [];
var cache_avtocomplete_contr = {};
var cache_avtocomplete_tovs = {};
var cache_avtocomplete_shops = {};
var cache_avtocomplete_brends = {};
var cache_contr_dialog = {};
var cache_shops_dialog = {};
var cache_shops_regions = {};
var cache_tovs_categs_dialog = {};
var cache_tovs_categs = {};
var cache_tovs_dialog = {};
var cache_kodNomenkatur = {};

var checkFields = [];
var selects_cats = ['#tovCategory','#tovGroup','#tovTipIsdeliya','#tovVidIsdeliya', '#tovBrendSelect'];
var selectsBrens = ['#division','#oblast','#city','#shop'];
var select2Option = {
	width:'180',
	minimumResultsForSearch:Infinity,
};

var copied_index = null;
var select2OptionBrends = {
	width:'180',
	ajax:{
		delay: 300,
	    url: "/brends/getBrendsForAvtocomplete",
	    dataType: 'json',
	    cache: true,
	    language: "ru",
		data: function (params) {
		    return {
		        term: params.term
			};
		},
		processResults: function (data) {
			let newData = [];
			for(ind in data)
			{
				newData[ind] = {'id':data[ind].val, 'text':data[ind].label};
			}
			return {
				results:newData
			};
		}
	}
};

$(function(){

	$('#tabs').tabs();
	// events();

/* head start */

	$('.addProcessForm').keydown(function(e)
	{
		if(e.keyCode == 13)
		{
			e.preventDefault();
			return false;
        }
	});

	$.each(selects_cats, function(index, value){
		if(selects_cats[index] == '#tovBrendSelect')
		{
			$(value).select2(select2OptionBrends);
		}
		else
		{
			$(value).select2(select2Option);
			$(value).on('change.select2', function(e, u){

				if($(this).val() > 0)
				{
					$(value).next().find('.select2-selection').removeClass('error_input');
				}
				var params = [];
				params['id'] = selects_cats[index];
				change_select2_cats(e, this, params);
			});
		}
	});

	$.each(selectsBrens, function(index, value){

		$(value).change(function(){

			if($('#division').val() == 0 && 
				$('#oblast').val() == 0 &&
				$('#city').val() == 0 &&
				$('#shop').val() == 0)
			{
				$('#shopsIskluchTitle').prop('disabled', false);
				$('#shopsIskluchTitle').parents('.form-field-input').removeClass('disabledField');
			}
			else if($(this).val() != '')
			{
				$('#shopsIskluch').val('');
				$('#shopsIskluchTitle').prop('disabled', true);
				$('#shopsIskluchTitle').parents('.form-field-input').addClass('disabledField');
			}
		});

		$(value).select2(select2Option);
		$(value).on('change.select2', function(e){
			var params = [];
			params['id'] = selectsBrens[index];
			change_select2_cats(e, this, params);
		});
	});

	$('#start_date').mask('ZZ-ZZ-ZZZZ', {
	    placeholder: "00-00-0000",
		translation:{
  			'Z': {
    			pattern: /[\-0-9]*/
  			}
		}
	});
	$('#end_date').mask('ZZ-ZZ-ZZZZ', {
		placeholder: "00-00-0000",
		translation:{
  			'Z': {
    			pattern: /[0-9]*/
  			}
		}
	});

	function tmp2(val)
	{
		var cur_date = new Date();
		var dateObj = $.datepicker.parseDate( "dd-mm-y", val);
		dateObj.setTime(dateObj.getTime() + 86400000);

		to.datepicker( "option", "minDate",  dateObj);
		$('.start_on_invoice_date').datepicker( "option", "maxDate", val);
	}

	if($.fn.datepicker)
	{
		var cur_d = new Date();
		var start = new Date(cur_d.getTime() + 86400000);
		var end = new Date(start + 86400000);

		var from = $('#start_date').datepicker({
			dateFormat: "dd-mm-y",
			minDate: start,
		}).change(function() {
			$(this).parents('.form-field-input').find('label').removeClass('error_input');
			tmp2(this.value);
			prepareDate(this);
		});

		var to = $('#end_date').datepicker({
			minDate: end,
			dateFormat: "dd-mm-y"
		}).change(function(){
			$(this).parents('.form-field-input').find('label').removeClass('error_input');
			prepareDate(this);
		});
	}

	if($("#process_type").length > 0)
	{
		$('#process_type').select2({
			width:'160',
			minimumResultsForSearch:Infinity
 		});
 		$("#process_type").on('change', function(evt) {
			$(this).parents('.form-field-input').find('label').removeClass('error_input');
		});
	}
/* head end */

/* таблица начало */
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	grid = $("#jqGridAdd");
	grid.jqGrid({
		height:300,
		width:500,
		shrinkToFit:true,
		colModel:[
			{label:'Действия',
				name:'actions',
				width:60,
				fixed:true,
				frozen:true,
				formatter:'actions',
				formatoptions:{ 
					keys: true,
					editbutton : true,
					delbutton : true,
					editformbutton: false,
					onEdit : function(){
						eventsJqGridRow();
					},
					delOptions : {
						url:'clientArray'
					},
					onSuccess: null,
					afterSave: null,
					onError: null,
					extraparam: {oper:'edit'},
					url:'clientArray',
				}
			},
	   		{label:'Товар <sup>*</sup>',
		   		name:'tovsTitles',
		   		width:250,
		   		edittype:'text',
				editable:true,
		   		fixed:true,
		   		resizable:false,
		   		title:false,
		   		frozen:true},
	   		{label:'Код товара <sup>*</sup>',
		   		name:'kodTov',
		   		align:"center",
		   		width:150,
		   		edittype:'text',
				editable:true,
		   		fixed:true,
		   		resizable:false,
		   		title:false,
		   		frozen:true},
	   		{label:'Магазин <sup>*</sup>',
		   		name:'shopsTitles',
		   		align:"center",
		   		width:250,
		   		edittype:'text',
				editable:true,
		   		fixed:true,
		   		resizable:false,
				editoptions:{
					dataInit:function(elem){
						attachDialogBtn(elem, 'getShopsErarhi')
					}
				},
		   		title:false,
		   		frozen:true},

	   		{label:'Дата начала акции <sup>*</sup>',
		   		name:'start_action_date',
		   		align:"center",
		   		width:200,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskDate').addClass('start_action_date');
					}
				},
		   		fixed:true},
	   		{label:'Дата окончания акции <sup>*</sup>',
		   		name:'end_action_date',
		   		align:"center",
		   		width:200,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskDate').addClass('end_action_date');
					}
				},
		   		fixed:true},
	   		{label:'Дистрибьютор',
		   		name:'distrTitles',
		   		align:"center",
		   		width:250,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						attachDialogBtn(elem, 'getContagentsErarhi')
					}
				},
		   		fixed:true},
	   		{label:'Бренд',
		   		name:'brendTitles',
		   		align:"center",
		   		width:250,
		   		edittype:'text',
				editable:true,
		   		title:false,
		   		fixed:true},
	   		{label:'Артикул ШК',
		   		name:'articule_sk',
		   		align:"center",
		   		width:250,
		   		edittype:'text',
				editable:true,
		   		title:false,
		   		fixed:true},
	   		{label:'Тип акции <sup>*</sup>',
		   		name:'type',
		   		align:"center",
		   		width:200,
		   		edittype:'select',
				editable:true,
				formatter:'select',
		   		title:false,
				editoptions:{value:action_types,
					dataInit:function(elem){
						$(elem).addClass('selectInRow');
						$(elem).find('option').each(function(){
							if($(this).attr('value') == 0)
								return;
							$(this).attr('onmouseover', 'showHint("'+$(this).attr('value')+'", "'+action_types_descr[$(this).attr('value')]+'")' )
						})
					}
				},
		   		fixed:true,
			},
			{label:'Размер скидки ON INVOICE (%)',
		   		name:'skidka_on_invoice',
		   		align:"center",
		   		width:180,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskProcent');
					}
				},
		   		fixed:true},
	   		{label:'% компенсации OFF INVOICE (%)',
		   		name:'kompensaciya_off_invoice',
		   		align:"center",
		   		width:190,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskProcent');
					}
				},
		   		fixed:true},
	   		{label:'Итого скидка (%) <sup>*</sup>',
		   		name:'skidka_itogo',
		   		align:"center",
		   		width:190,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskProcent');
					}
				},
		   		fixed:true},
	   		{label:'Старая розничная цена (руб)',
		   		name:'roznica_old',
		   		align:"center",
		   		width:180,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskPrice');
					}
				},
		   		fixed:true},
	   		{label:'Новая розничная цена (руб)',
		   		name:'roznica_new',
		   		align:"center",
		   		width:180,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskPrice');
					}
				},
		   		fixed:true},
	   		{label:'Старая закупочная цена (руб)',
		   		name:'zakup_old',
		   		align:"center",
		   		width:190,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskPrice');
					}
				},
		   		fixed:true},
	   		{label:'Новая закупочная цена (руб)',
		   		name:'zakup_new',
		   		align:"center",
		   		width:180,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskPrice');
					}
				},
		   		fixed:true},
	   		{label:'Дата начала скидки ON INVOICE',
		   		name:'start_date_on_invoice',
		   		align:"center",
		   		width:200,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskDate').addClass('start_on_invoice_date');
					}
				},
		   		fixed:true},
	   		{label:'Дата окончания скидки ON INVOICE',
		   		name:'end_date_on_invoice',
		   		align:"center",
		   		width:210,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskDate').addClass('end_on_invoice_date');
					}
				},
		   		fixed:true},

	   		{label:'Стоимость размещения',
		   		name:'razmesh_price',
		   		align:"center",
		   		width:210,
		   		edittype:'text',
				editable:true,
		   		title:false,
				editoptions:{
					dataInit:function(elem){
						$(elem).addClass('maskPrice');
					}
				},
		   		fixed:true},
	   		{label:'Подписи, слоганы, расшифровки и пояснения к товарам в рекламе.',
		   		name:'descr',
		   		align:"center",
		   		width:200,
		   		edittype:'text',
				editable:true,
		   		fixed:true},
	   		{label:'Пометки к товарам',
		   		name:'marks',
		   		width:150,
		   		edittype:'text',
				editable:true,
		   		align:"center",
		   		fixed:true},
		],
		multiselect:true,
		rownumbers: true,
		ondblClickRow:function(rowid)
		{
			grid.editRow(rowid);
			eventsJqGridRow();
		},
		onCellSelect: function(rowId, colId, c, event){

			if(colId == 2)
			{
				setTimeout(function(){
					if($(event.target).closest('#jSaveButton_'+rowId).length || 
						$(event.target).closest('#jCancelButton_'+rowId).length)
					{
						grid.jqGrid('resetSelection',rowId);
					}
					else if($(event.target).closest('#jEditButton_'+rowId).length)
					{
						grid.jqGrid('resetSelection',rowId);
						grid.jqGrid('setSelection',rowId);
					}
				}, 200);
			}
		},
	});
	grid.on('jqGridDelRowBeforeSubmit', function(e,id){
		delJqGridRows(id);
	});

	let r = addJqGridRow();
	grid.editRow(r);
	grid.setSelection(r);
	eventsJqGridRow();

	// только при скроле замораживаем столбцы
	$('#' + grid.attr('id')).parents('.ui-jqgrid-bdiv').scroll(function(){

		if($(this).scrollLeft() > 0)
		{
			if($('table[id$=_frozen]').length > 0)
			{
				return;
			}
			setFrozenColumns();
			setFrozenHeightTd();
			scrollLeftActionOnFrozen();
		}
		else
		{
			destroyFrozenColumns();
		}
	});

/* таблица конец */

	// выбор чекбоксов во всплывающем окне выбора
	$('#shops_dialog').on('click', 'input[type=checkbox]', function(e){
		e.stopPropagation();

		if($(this).is(':checked'))
			var chck = false;
		else
			var chck = true;

		//отмечаем/снимаем все дочерние чекбоксы
		$(this).next().next().find('input[type=checkbox]').each(function(){
			if(chck)
				$(this).prop('checked',false);
			else
				$(this).prop('checked',true);
		});

		//отмечаем/снимаем все родительские чекбоксы
		$(this).parents('li').each(function(){
			var ch = $(this).find(' > input[type=checkbox]');
			if(chck)
				$(ch).prop('checked',false);
		});
	});

	//открываем/скрываем разделы(папки) в окне выбора
	$('#shops_dialog').on('click', ' ul li', function(e){

		if($(this).find(' > ul').length > 0)
		{
			e.preventDefault();
		}
		e.stopPropagation();

		if($(this).hasClass('active'))
		{
			$(this).removeClass('active');
		}
		else
		{
			$(this).addClass('active');
		}
	});

	$('div').on('click', '.field_input_file > .file, td .dialog', function(){

		var el = this;
		if($(el).parents('.disabledField').length > 0)
		{
			return;
		}
		if($(el).data('type') == 'getShopsErarhi' || $(el).data('type') == 'getShopsErarhiIsk')
		{
			$("#shops_dialog").dialog({
				title: "Выбор магазинов",
				open:function( event, ui){

					if($(el).data('type') == 'getShopsErarhiIsk')
					{
						get_shop_list( $(el).prev().val().split(';') );
					}
					else
					{
						var n = getJqGridRowNumber(el);
						if(dopData['shops'] && dopData['shops'][n])
						{
							var data = dopData['shops'][n].split(';');
						}
						else
						{
							var data = [];
						}
						get_shop_list( data );
					}
				},
				resizable:true,
				width:500,
				modal:true,
				position:{
					my:"top+5%",
					at:"top+5%",
					of:window
				},
				maxHeight:$(window).height()-50,
				closeOnEscape:true,
				buttons:{
				    "Выбрать магазин": function(e){

						var ids=[],titles=[];
						$('#shops_dialog input[value]:checked').each(function(){
							ids.push($(this).val());
							titles.push($(this).data('title'));
						});

						titles = titles.sort().join('; ');
						var row_n = getJqGridRowNumber(el);
						if(row_n)
						{
							//в таблице
							$('#'+row_n+'_shopsTitles').val(titles);
							checkAddField(row_n, 'chShop', titles);

							$('#'+row_n+'_shopsTitles').trigger('change');
							addDopData(row_n, 'shops', ids.join(';'));
						}
						else
						{
							//в фильтре
							$(el).parents('.field_input_file').find('input.shops').val(ids.join(';'));
							$(el).parents('.field_input_file').find('input.shopsTitles').val(titles);
							$(el).parents('.field_input_file').find('input.shopsTitles').trigger('change');
						}
						$("#shops_dialog").dialog("close");
				    }
				}
			});
		}
		else if($(el).data('type') == 'getContagentsErarhi')
		{
			$("#contragent_dialog").dialog({
				title: "Выбор контрагента",
				closeOnEscape:true,
				width:400,
				modal:true,
				position:{
					my:"top+5%",
					at:"top+5%",
					of:window
				},
				maxHeight:$(window).height()-50,
				close:function(){
					$("#contragent_dialog").html('');
				},
				open:function( event, ui )
				{
					let selIds;
					var n = getJqGridRowNumber(el);
					if(dopData['distr'] && dopData['distr'][n])
					{
						selIds = dopData['distr'][n].split(',');
					}
					else
					{
						selIds = [];
					}

					$("#contragent_dialog").dialog( "option", 'selAgents', selIds);
					get_contragents_list();
				},
				buttons:{
				    "Выбрать контрагента": function(e){
						var ids=[], titles=[];

						$('#contragent_dialog input[value]:checked').each(function(){
							ids.push($(this).val());
							titles.push($(this).data('title'));
						});

						var row_n = getJqGridRowNumber(el);
						checkAddField(row_n, 'chDistr', titles.join(';  '));
						$('#'+row_n+'_distrTitles').val(titles.join(';  '));
						addDopData(row_n, 'distr', ids.join());
						$("#contragent_dialog").dialog("close");
					}
				}
			});
		}
	});


	$('#fillTableFromFile').click(function(e){
		e.preventDefault();

		show_load();

		var formData = new FormData();
		jQuery.each($('input[type=file]')[0].files, function(i, file) {
			formData.append('file', file);
		});
		var err = false;

		if($('#start_date').val() == '')
		{
			$('#start_date').addClass('error_input')
			err = true;
		}
		else
		{
			$('#start_date').removeClass('error_input')
		}

		if($('#end_date').val() == '')
		{
			err = true;
			$('#end_date').addClass('error_input')
		}
		else
		{
			$('#end_date').removeClass('error_input')
		}

		if(err)
		{
			hide_load();
			return;
		}

		formData.append('start_date', $('#start_date').val());
		formData.append('end_date', $('#end_date').val());

		//	отправляем через ajax
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			url:"/processes/prepareDataFromFile",
			type:"post",
			dataType:"json",
			cache:false,
			contentType:false,
			processData:false,
			data:formData,
			success:function(data)
			{
				hide_load();

				if(data['errors'])
				{
					var str = '';
					var tr_str = [];
					for(ind in data['errors'])
					{
						str += '<br><b>В строке "'+ind+'" обнаружены следующие ошибки:</b> <br>';
						for(index in data['errors'][ind])
						{
							str += data['errors'][ind][index] + '<br>';
							if($.inArray(ind, tr_str) < 0)
							{
								tr_str.push(ind);
							}
						}
						delete(data['data'][ind]);
					}

					if(tr_str.length > 0)
					{
						str = 'Не загружены строки из файла с номерами: '+tr_str.join(', ')+'<br><br>'+str;
					}

					if(str != '')
					{
						showMessage('error', false, str, {
							width:800
						});
					}
				}

				for(ind in data['data'])
				{
					if(data['data'][ind]['shopsTitles'])
					{
						data['data'][ind]['shopsTitles'] = data['data'][ind]['shopsTitles'].join('; ');						
					}
					if(data['data'][ind]['brend'])
					{
						data['data'][ind]['brendTitles'] = data['data'][ind]['brendTitles'];
					}

					var r = addJqGridRow(data['data'][ind]);
					if(r)
					{
						checkAddField(r, 'chTitle', data['data'][ind]['tovsTitles']);
						checkAddField(r, 'chKod', data['data'][ind]['kodTov']);
						checkAddField(r, 'chShop', data['data'][ind]['shopsTitles']);
						checkAddField(r, 'chDistr', data['data'][ind]['distrTitles']);
						checkAddField(r, 'chBrendTitles', data['data'][ind]['brend']);

						if(typeof(data['data'][ind]['shops']) != 'undefined')
						{
							addDopData(r, 'shops', data['data'][ind]['shops'].join(';'));
						}
						if(typeof(data['data'][ind]['distr']) != 'undefined')
						{
							addDopData(r, 'distr', data['data'][ind]['distr']);
						}
						if(typeof(data['data'][ind]['brendId']) != 'undefined')
						{
							addDopData(r, 'brend', data['data'][ind]['brendId']);
						}
					}
				}
				eventsJqGridRow();

				$(window).trigger('resize');
				$('.hideBlock').hide();
			}
		});
	});

	$('#fillTable').click(function(){
		var err = false;
		var arr = {};
		show_load();

		if($('#tovCategory').val() == 0)
		{
			$('#select2-tovCategory-container').parents('.select2-selection').addClass('error_input');
			err = true;
		}
		else
		{
			$('#select2-tovCategory-container').parents('.select2-selection').removeClass('error_input');
			arr.tovCategory = $('#tovCategory').val();
		}

		if($('#tovGroup').val() == 0)
		{
			$('#select2-tovGroup-container').parents('.select2-selection').addClass('error_input');
			err = true;
		}
		else
		{
			$('#select2-tovGroup-container').parents('.select2-selection').removeClass('error_input');
			arr.tovGroup = $('#tovGroup').val();
		}

		if($('#tovTipIsdeliya').val() == 0)
		{
			$('#select2-tovTipIsdeliya-container').parents('.select2-selection').addClass('error_input');
			err = true;
		}
		else
		{
			$('#select2-tovTipIsdeliya-container').parents('.select2-selection').removeClass('error_input');
			arr.tovTipIsdeliya = $('#tovTipIsdeliya').val();
		}

		$('#select2-tovVidIsdeliya-container').parents('.select2-selection').removeClass('error_input');
		arr.tovVidIsdeliya = $('#tovVidIsdeliya').val();


		if($('#tovBrendSelect').val() == 0)
		{
			$('#select2-tovBrendSelect-container').parents('.select2-selection').addClass('error_input');
			err = true;
		}
		else
		{
			$('#select2-tovBrendSelect-container').parents('.select2-selection').removeClass('error_input');
			arr.tovBrend = $('#tovBrendSelect').val();
		}

		arr.division = $('#division').val();
		arr.oblast = $('#oblast').val();
		arr.city = $('#city').val();
		arr.shop = $('#shop').val();
		arr.shopsIskluch = $('#shopsIskluch').val();

		// if(arr.shop == 0 && arr.city == 0 && arr.oblast == 0 && arr.division == 0 && arr.shopsIskluch == 0)
		// {
		// 	$('#shopsIskluchTitle').addClass('error_input');
		// 	err = true;
		// }
		// else
		// {
		// 	$('#shopsIskluchTitle').removeClass('error_input');
		// }

		if(!err)
		{
			$('.hideBlock').hide();
			getTovsToFillTable(arr, 0);
		}
		else
		{
			hide_load();
		}
	});
});

function showPanel(btn, selector)
{
	$('.hideBlock').not(selector).hide();

	$(selector).css('top', $(btn).offset().top + $(btn).outerHeight()+7);
	$(selector).toggle();
}

function getTovsToFillTable(arr, page)
{
	arr.page = page;
	$.ajax({
		timeout:0,
		url:"/sys/getTovsToFillTable",
		data:arr,
		dataType:'json',
		success: function(data){

			hide_load();

			if(data == 0)
			{
				alert('По указанным критериям товары не найдены');
				return;
			}

			if(data.need > 0)
			{
				getTovsToFillTable(arr, data.need);
			}

			for(ind in data.items)
			{
				// if($('.kodTov[value='+data.items[ind].c+']').length == 0)
				// {
					dataToFill = {};

					// dataToFill['shops'] = [];
					dataToFill.shopsTitles = [];
					dataToFill.kodTov = data.items[ind].c;
					dataToFill.tovsTitles = data.items[ind].n;
					dataToFill.start_action_date = '';
			   		dataToFill.end_action_date = '';
			   		dataToFill.distrTitles = '';
			   		dataToFill.brendTitles = '';
			   		dataToFill.articule_sk = '';
			   		dataToFill.type = '';
			   		dataToFill.skidka_on_invoice = '';
			   		dataToFill.kompensaciya_off_invoice = '';
			   		dataToFill.skidka_itogo = '';
			   		dataToFill.roznica_old = '';
			   		dataToFill.roznica_new = '';
			   		dataToFill.zakup_old = '';
			   		dataToFill.zakup_new = '';
			   		dataToFill.start_date_on_invoice = '';
			   		dataToFill.end_date_on_invoice = '';
			   		dataToFill.razmesh_price = '';
			   		dataToFill.descr = '';
			   		dataToFill.marks = '';

					for(ind2 in data.shop)
					{
						dataToFill.shopsTitles[ind2] = data.shop[ind2].title;
					}
					dataToFill.shopsTitles = dataToFill.shopsTitles.join('; ');
					addJqGridRow(dataToFill);
			// }
			}

			// delRows(true);
			clearFilter();
			eventsJqGridRow();
			$(window).trigger('resize');
		}
	});
}

function clearFilter()
{
	for(ind in selects_cats)
	{
		$(selects_cats[ind]).val("0").trigger("change");
	}
	for(ind in selectsBrens)
	{
		$(selects_cats[ind]).val("0").trigger("change");
	}
	$('.hideBlock').each(function(){
		$(this).find('input[type=text]').val('');
	});
}

function build_contragents_list(cache_contragents)
{
	var root = $('#contragent_dialog').html('');
	root.append('<ul>');

	$('img.load_img').remove();
	var li = '';

	for(ind in cache_contragents)
	{
		var ch = '';
		if($.inArray(cache_contragents[ind]['val'], $("#contragent_dialog").dialog( "option", 'selAgents')) >= 0)
		{
			ch = 'checked="checked"';
		}
		li += '<li><input type="radio" name="postavshik" id="c_'+cache_contragents[ind]['val']+'" value="'+cache_contragents[ind]['val']+'" '+
			ch+
			' data-title="'+escape(cache_contragents[ind]['label'])+'" >'+
			'<label for="c_'+cache_contragents[ind]['val']+'">'+cache_contragents[ind]['label']+'</label></li>';
	}
	root.find(' > ul').append(li);
}

function get_contragents_list()
{
	show_load('#contragent_dialog');

	if($.isEmptyObject(cache_contr_dialog))
	{
		$.ajax({
			url: "/sys/getContragentsErarhi",
			dataType:'json',
			success: function(data){
				cache_contr_dialog = data;
				build_contragents_list(data);
			}
		});
	}
	else
	{
		build_contragents_list(cache_contr_dialog);
	}
}

function escape(string) {
    var htmlEscapes = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&apos;',
        ",": ''
	};

	return string.replace(/[&<>",']/g, function(match) {
        return htmlEscapes[match];
    });
};

function fillCategsSelect2Filter(id, data)
{
	var select = $(id);
	select.find('option:not(:eq(0))').remove();

	if(id == '#tovGroup')
	{
		$('#tovTipIsdeliya option:not(:eq(0))').remove();
		$('#tovTipIsdeliya').select2(select2Option).trigger('change');

		$('#tovBrendSelect option:not(:eq(0))').remove();
		$('#tovBrendSelect').select2(select2Option).trigger('change');
	}

	if(id == '#tovGroup' || id == '#tovTipIsdeliya')
	{
		$('#tovVidIsdeliya option:not(:eq(0))').remove();
		$('#tovVidIsdeliya').select2(select2Option).trigger('change');

		$('#tovBrendSelect option:not(:eq(0))').remove();
		$('#tovBrendSelect').select2(select2Option).trigger('change');
	}

	if(id == '#oblast' || id == '#division')
	{
		$('#city option:not(:eq(0))').remove();
		$('#city').select2(select2Option).trigger('change');
	}

	if(id == '#oblast' || id == '#city' || id == '#division')
	{
		$('#shop option:not(:eq(0))').remove();
		$('#shop').select2(select2Option).trigger('change');
	}

	for(var i = data.length - 1; i >= 0; i--)
	{
		let op = $('<option />');
		op.text(data[i].title);
		op.attr('value', data[i].id);
		select.append(op);
	}

	if(id == '#tovBrendSelect')
	{
		if(select.find('option').length <= 1)
		{
			select.select2(select2OptionBrends).trigger('change');
		}
		else
		{
			select.select2(select2Option);
		}
	}
	else
	{
		select.select2(select2Option).trigger('change');
		select.select2(select2Option).on('change.select2', function(e){
			var params = [];
			params['id'] = id;
			change_select2_cats(e, this, params);
		});
	}
}

function change_select2_cats(evt, this_el, params)
{
	if($.inArray(params['id'], selects_cats) >= 0)
	{
		var ind = selects_cats.indexOf(params['id']);
		var cache = cache_tovs_categs;
		var selects = selects_cats;
	}
	else
	{
		var ind = selectsBrens.indexOf(params['id']);
		var cache = cache_shops_regions;
		var selects = selectsBrens;
	}

	if(!cache[$(this_el).val()] || $.isEmptyObject(cache[$(this_el).val()]))
	{
		if(params['id'] == '#tovVidIsdeliya' || params['id'] == '#tovTipIsdeliya')
		{
			if($(this_el).val() > 0)
			{
				$.ajax({
					url: "/brends/getBrendsForCategs/"+$(this_el).val(),
					dataType:'json',
					success: function(data){
						cache[$(this_el).val()] = data;
						fillCategsSelect2Filter(selects[4], data);
					}
				});
			}
		}

		if($.inArray(params['id'], selects_cats) >= 0 && params['id'] != '#tovBrendSelect')
		{
			if($(this_el).val() > 0)
			{
				$.ajax({
					url: "/sys/getSubCategs/"+$(this_el).val(),
					dataType:'json',
					error: function(data) {
						fillCategsSelect2Filter(selects[ind+1], []);
					},
					success: function(data) {
						cache[$(this_el).val()] = data;
						fillCategsSelect2Filter(selects[ind+1], data);
					}
				});				
			}
		}
		else if(params['id'] == '#city')
		{
			$.ajax({
				url: "/sys/getShopsForRegion/"+$(this_el).val(),
				dataType:'json',
				success: function(data){
					cache_shops_regions[$(this_el).val()] = data;
					fillCategsSelect2Filter(selectsBrens[ind+1], data);
				}
			});
		}
		else if($.inArray(params['id'], selectsBrens) >= 0 )
		{
			$.ajax({
				url: "/sys/getSubRegions/"+$(this_el).val(),
				dataType:'json',
				error: function(data) {
					fillCategsSelect2Filter(selectsBrens[ind+1], []);
				},
				success: function(data){
					cache_shops_regions[$(this_el).val()] = data;
					fillCategsSelect2Filter(selectsBrens[ind+1], data);
				}
			});
		}
	}
	else
	{
		fillCategsSelect2Filter(selects[ind+1], cache[$(this_el).val()]);
	}
}
function get_shop_list(ids)
{
	if($.isEmptyObject(cache_shops_dialog))
	{
		$.ajax({
			url: "/sys/getShopsErarhi",
			dataType:'json',
			success: function(data){
				cache_shops_dialog = data;
				build_list(data, ids);
			}
		});
	}
	else
	{
		build_list(cache_shops_dialog, ids);
	}
}
function build_list(cache_shops_dialog, ids)
{
	$('#shops_dialog').html('');
	var ul = $('<ul>');
	ul.addClass("tree");
	$('#shops_dialog').append(ul);

	var li = '';
	for(ind in cache_shops_dialog)
	{
		var display = '';

		var macreg = 'checked="checked"';
		var lii = '';
		for(inde in cache_shops_dialog[ind])
		{
			if(!cache_shops_dialog[ind][inde]['title'])
				continue;

			reg_ch = 'checked="checked"';
			var liii = '';
			for(index in cache_shops_dialog[ind][inde])
			{
				if(!cache_shops_dialog[ind][inde][index]['title'])
					continue;

				city_ch = 'checked="checked"';
				var li4 = '';
				for(indexx in cache_shops_dialog[ind][inde][index])
				{
					if(indexx == 'title')
						continue;

					ch = '';
					if($.inArray(indexx, ids) >= 0)
					{
						ch = 'checked="checked"';
						display = ' class="active" ';
					}
					else
					{
						city_ch = '';
					}
					li4 += '<li class="no_icon"><input type="checkbox" value="'+indexx+'" id="shop'+indexx+'" data-title="'+escape(cache_shops_dialog[ind][inde][index][indexx])+'" '+ch+'>'+
						'<label for="shop'+indexx+'">Магазин '+cache_shops_dialog[ind][inde][index][indexx]+'</label></li>';
				}
				if(city_ch == '')
					reg_ch = '';

				liii += '<li '+display+'><input type="checkbox" '+city_ch+'>'+
					'<label>'+cache_shops_dialog[ind][inde][index]['title']+'</label><ul>'+li4+'</ul></li>';
			}

			if(reg_ch == '')
				macreg = '';

			lii += '<li '+display+'><input type="checkbox" '+reg_ch+'>'+
				'<label>'+cache_shops_dialog[ind][inde]['title']+'</label><ul>'+liii+'</ul></li>';
		}

		li += '<li '+display+'><input type="checkbox" '+macreg+'>'+
			'<label>'+cache_shops_dialog[ind]['title']+'</label><ul>'+lii+'</ul></li>';
	}
	$('#shops_dialog > ul').append(li);
	$('#shops_dialog').prepend($('<input style="margin:0 3px 8px 9px ;" type="checkbox" id="checkAllShop"><label style="margin:0 0 8px 0;" for="checkAllShop">Выбрать все магазины</label>'));
}

function prepareDate(el)
{
	var tmp = el.value.split('-');
	if(tmp[2] && tmp[2].length == 2)
	{
		tmp[2] = '20'+tmp[2];
	}
	el.value = tmp.join('-');
}

function illumination(el, times)
{
	if(times > 7)
	{
		$(el).css('background', '#fff');
		return;
	}

	if($(el).css('background-color') == 'rgb(255, 255, 255)')
	{
		$(el).css('background-color', '#fdf5ce');
	}
	else
	{
		$(el).css('background-color', '#fff');
	}

	times++;
	setTimeout(function(){illumination(el, times)}, 200);
}

function showHint(id, text)
{
	hideHint();
	if(text == '')
		return;

	var d = $('<div />');
	d.css('position', 'absolute');
	d.addClass('type_descr');
	d.css('top', $('.select2-results__options li[id$=-'+id+']').offset().top);
	d.css('left', $('.select2-results__options li[id$=-'+id+']').offset().left-150);
	d.css('width', '150');
	d.css('height', 'auto');
	d.css('min-height', '100');
	d.css('background', '#fff');
	d.css('padding', '7px');
	d.css('z-index', '100');
	d.css('font-size', '12px');
	d.css('line-height', '14px');
	d.css('border', '1px solid #ccc');
	d.html(text);
	$('body').append(d);
}
function hideHint()
{
	$('.type_descr').remove();
}

function addJqGridRow(initData)
{
	var  r = 0;
	if($(grid).find('tr.jqgrow:last').length > 0)
	{
		r = parseInt($(grid).find('tr.jqgrow:last').attr('id'));
	}

	var parameters =
	{
		rowID:++r,
	    initdata:initData,
	    position:"last",
	    useDefValues:true,
	    useFormatter:false,
	    addRowParams:{extraparam:{}}
	}
	grid.jqGrid('addRow',parameters);

	if(initData)
	{
		if( typeof(initData.shopsTitles) != 'undefined')
		{
			checkAddField(r, 'chShop', initData.shopsTitles);
		}
		if(typeof(initData.shops) != 'undefined')
		{
			addDopData(r, 'shops', initData.shops);
		}

		if(typeof(initData.kodTov) != 'undefined')
		{
			checkAddField(r, 'chKod', initData.kodTov);
		}

		if(typeof(initData.tovsTitles) != 'undefined')
		{
			checkAddField(r, 'chTitle', initData.tovsTitles);
		}

		if(typeof(initData.distrTitles) != 'undefined')
		{
			checkAddField(r, 'chDistr', initData.distrTitles);
		}
	}
	return r;
}
function addJqGridRowFromPanel()
{
	let r = addJqGridRow();
	grid.editRow(r);
	grid.setSelection(r);
	eventsJqGridRow();
}

function addJqGridSubmit()
{
	show_load();

	grid.find('tr[editable=1]').each(function(){
		grid.saveRow(this.id, false, 'clientArray');
	});

	let arrData = '';
	arrData += 'rows='+JSON.stringify( grid.getRowData() );
	for(ind in dopData)
	{
		arrData += '&rowsDopData['+ind+']=' + JSON.stringify(dopData[ind]);
	}
	arrData += '&process_type='+ $('#process_type').val();
	arrData += '&process_title=' + $('#process_title').val();
	arrData += '&start_date=' + $('#start_date').val();
	arrData += '&end_date=' + $('#end_date').val();

	$.ajax({
		url:'/processes/ajaxAdd',
		type:'post',
		dataType:'json',
		data:arrData,
		success:function(data){

			hide_load();

			if(data['errors'])
			{
				var str = '';
				var tr_str = [];
				for(ind in data['errors'])
				{
					str += '<br><b>В строке "'+ind+'" обнаружены следующие ошибки:</b> <br>';
					for(index in data['errors'][ind])
					{
						str += data['errors'][ind][index] + '<br>';
						if($.inArray(ind, tr_str) < 0)
						{
							tr_str.push(ind);
						}
					}
					delete(data['data'][ind]);
				}

				if(tr_str.length > 0)
				{
					str = 'Не загружены строки из файла с номерами: '+tr_str.join(', ')+'<br><br>'+str;
				}

				if(str != '')
				{
					showMessage('error', false, str, {
						width:800
					});
				}
			}
			else if(data['success'] && data['success'] == 1)
			{
				window.location.href = '/processes';
			}

		}
	});
}



function setFrozenColumns()
{
	if($('table[id$=_frozen]').length == 0)
	{
		grid.jqGrid('setFrozenColumns');

		//изменяем id-шники у замороженных столбцов
		setTimeout(function(){

			// checkbox
			let tmp = $('.frozen-div #cb_'+grid.attr('id')).attr('id');
			tmp = tmp.split('_');
			tmp[0] = 'frozen_'+tmp[0];
			$('.frozen-div #cb_'+grid.attr('id')).attr('id', tmp.join('_'));

			// инпуты в замороженных колонках
			$('table[id$=_frozen]').find('tr').each(function(i){
				$(this).find('td').each(function(index){

					var inpt = $('#'+grid.attr('id')+'_frozen tr:eq('+i+') td:eq('+index+') > input');
					if(!inpt.attr('id'))
						return;
					var tmp = inpt.attr('id').split('_');
					tmp[0] = 'frozen_'+tmp[0];
					inpt.attr('id', tmp.join('_'));
				});
			});
		}, 200);
	}
}

function destroyFrozenColumns()
{
	if($('table[id$=_frozen]').length > 0)
	{
		grid.jqGrid('destroyFrozenColumns');
	}
}

function scrollLeftAction()
{
	$('#'+grid.attr('id')).parents('.ui-jqgrid-bdiv').scrollLeft(0);
}

function scrollLeftActionOnFrozen(){

	setTimeout(function(){

		if($('#frozen_cb_' + grid.attr('id')).length > 0)
		{
			$('#frozen_cb_' + grid.attr('id')).click(function(){
				scrollLeftAction();

				var el = $('#cb_' + grid.attr('id'));
				if(!el.prop('checked'))
				{
					el.prop('checked', true);
				}
				else
				{
					el.prop('checked', false);
				}
			});
		}

		var tbl = $('table[id$=_frozen]');
		if(tbl.length > 0)
		{
			tbl.find('tr').each(function(i){
				$(this).find('td input[id^=frozen_]').each(function(ind){
					$(this).focus(function(){

						scrollLeftAction();

						let id = $(this).attr('id').replace('frozen_', '');
						$('#'+id).focus();
					});
				});
			});
		}
	}, 200);
}

function delJqGridRows(id)
{
	if(id)
	{
		grid.jqGrid('delRowData', id);
		return;
	}

	let ids = grid.jqGrid('getGridParam','selarrrow');
	for (var i = ids.length - 1; i >= 0; i--) {

		grid.jqGrid('delRowData', ids[i]);
	}
}

function getJqGridRowNumber(el)
{
	return $(el).parents('tr').attr('id');
}

function eventsJqGridRow()
{
	// $('input[id$=_kod]').autocomplete(kodNomenkaturAvtocomplete);

	// let d = new Date();
	// let min = new Date();
	// min.setTime(d.getTime() + 86400000);
	// let max = new Date();
	// max.setTime(d.getTime() + 6048000000);
	// $('input[id$=_srok]').datepicker({
	// 	minDate:min,
	// 	maxDate:max,
	// });

	// $('input[type=text]').on('change', function(){
	// 	$(this).parents('td').find('.error_message').remove();
	// });

	$('input[id$=_kodTov]').autocomplete({
		source: function( request, response ){
			var term = request.term;
			request.kod = true;

			if ( term in cache_avtocomplete_tovs )
			{
				response( cache_avtocomplete_tovs[term]);
				return;
			}

			$.getJSON( "/tovs/ajaxGetTovarForAvtoComplete", request, function( data, status, xhr ) {
				cache_avtocomplete_tovs[term] = data;
				response(data);
			});
		},
		minLength:2,
		change:function()
		{
			var n = getJqGridRowNumber(this);
			if(checkFields[n] &&
				checkFields[n]['chKod'] &&
				$(this).val() != checkFields[n]['chKod'])
			{
				$(this).val(checkFields[n]['chKod']);
			}
			$('#'+n+'_tovsTitles').trigger('change');
		},
		select: function( event, ui )
		{
			var n = getJqGridRowNumber(this);
			$(this).val(ui.item.value);

			checkAddField(n, 'chTitle', ui.item.val);
			checkAddField(n, 'chKod', ui.item.value);

			$('#'+n+'_tovsTitles').val(ui.item.val);
		}
	});

	$('input[id$=_tovsTitles]').autocomplete({
		source: function( request, response ){
			var term = request.term;
			if ( term in cache_avtocomplete_tovs )
			{
				response( cache_avtocomplete_tovs[term]);
				return;
			}
			$.getJSON( "/tovs/ajaxGetTovarForAvtoComplete", request, function( data, status, xhr ) {
				cache_avtocomplete_tovs[term] = data;
				response(data);
			});
		},
		minLength: 2,
		change:function()
		{
			var n = getJqGridRowNumber(this);
			if(checkFields[n] &&
				checkFields[n]['chTitle'] &&
				$(this).val() != checkFields[n]['chTitle'])
			{
				$(this).val(checkFields[n]['chTitle']);
			}
			$('#'+n+'_kodTov').trigger('change');
		},
		select: function( event, ui )
		{
			$(this).val(ui.item.value);

			var n = getJqGridRowNumber(this);
			checkAddField(n, 'chTitle', ui.item.label);
			checkAddField(n, 'chKod', ui.item.val);

			$('#'+n+'_kodTov').val(ui.item.val);
		}
	});

	$('input[id$=_shopsTitles]').autocomplete({
		source: function( request, response ){
			if(request.term.length > 100)
			{
				return;
			}
			var term = request.term;
			if ( term in cache_avtocomplete_shops )
			{
				response( cache_avtocomplete_shops[term]);
				return;
			}
			$.getJSON("/shop/ajaxGetShops", request, function( data, status, xhr ) {
				cache_avtocomplete_shops[term] = data;
				response(data);
			});
		},
		minLength: 2,
		select: function( event, ui )
		{
			var n = getJqGridRowNumber(this);
			checkAddField(n, 'chShop', ui.item.value);
			addDopData(n, 'shops', ui.item.val);
		},
		change: function( event, ui )
		{
			var n = getJqGridRowNumber(this);
			if(checkFields[n] &&
				checkFields[n]['chShop'] &&
				$(this).val() != checkFields[n]['chShop'])
			{
				$(this).val(checkFields[n]['chShop']);
			}
		}
	});

	$('input[id$=_distrTitles]').autocomplete({
		source: function( request, response ){
			var term = request.term;
			if ( term in cache_avtocomplete_contr )
			{
				response(cache_avtocomplete_contr[term]);
				return;
			}
			$.getJSON( "/sys/getContragentsForAvtocomplete", request, function( data, status, xhr ) {
				cache_avtocomplete_contr[term] = data;
				response(data);
			});
		},
		minLength: 2,
		change:function()
		{
			var n = getJqGridRowNumber(this);
			if(checkFields[n] &&
				checkFields[n]['chDistr'] &&
				$(this).val() != checkFields[n]['chDistr'])
			{
				$(this).val(checkFields[n]['chDistr']);
			}
		},
		select: function(event, ui) {
			var n = getJqGridRowNumber(this);
			checkAddField(n, 'chDistr', ui.item.value);
			addDopData(n, 'distr', ui.item.val);
		}
	});

	$('input[id$=_brendTitles]').autocomplete({
		source: function( request, response ){
			var term = request.term;
			if ( term in cache_avtocomplete_brends )
			{
				response(cache_avtocomplete_brends[term]);
				return;
			}
			$.getJSON( "/brends/getBrendsForAvtocomplete", request, function( data, status, xhr ) {
				cache_avtocomplete_brends[term] = data;
				response(data);
			});
		},
		minLength: 2,
		change:function()
		{
			var n = getJqGridRowNumber(this);
			if(checkFields[n] &&
				checkFields[n]['chBrendTitles'] &&
				$(this).val() != checkFields[n]['chBrendTitles'])
			{
				$(this).val(checkFields[n]['chBrendTitles']);
			}
		},
		select: function(event, ui) {
			var n = getJqGridRowNumber(this);
			checkAddField(n, 'chBrendTitles', ui.item.label);
			addDopData(n, 'brend', ui.item.val);
		}
	});

	$('.selectInRow').select2(select2Option).change(function(){
		hideHint();
	});
	$('.selectInRow').on('select2:close', function (e) {
	  hideHint();
	});

	if($.fn.mask)
	{
		$('.maskProcent').mask('ZZZZZ', {
		    placeholder: "0 %",
			onKeyPress: function(cep, event, currentField, options){

				var reg2 = new RegExp("[^\.\,0-9]+");
				var reg = new RegExp("[0-9]{1,3}[(\.|\,)]*[0-9]{0,2}");

				if(parseFloat(cep) >= 100 || parseFloat(cep) < 0 || !reg.test(cep) || reg2.test(cep))
				{
					$(currentField).val('');
				}
			},
			translation:{
      			'Z': {
        			pattern: /[\.\,0-9]*/
      			}
    		}
		});

		$('.maskPrice').on('keyup', function(e){
			var str = $(this).val();
			var reg = /[^\.\,0-9]+/g;

			if(reg.test(str))
			{
				str = str.replace(reg, '');
			}

			var result = '';
			str = str.replace(/[\.\,]+/g, '.');
			
			var point_ind = str.indexOf('.');
			if(point_ind > 0)
			{
				var result_st = str.slice(0, point_ind)
				var result_end = str.slice(point_ind);
			}
			else
			{
				var result_st = str;
				var result_end = '';
			}

			var arr = result_st.split('');
			arr.reverse();

			$.each(arr, function(index){

				if(index%3 == 0)
				{
					result = this+' '+result;
				}
				else
				{
					result = this + result;
				}
			});

			result += result_end;
			result = result.replace(/[ ]*\.[ ]*/g, '.').trim();
			$(this).val(result);
		});

		$('.maskDate').mask('ZZ-ZZ-ZZZZ', {
		    placeholder: "01-01-2001",
			onKeyPress: function(cep, event, currentField, options){

				var tmp = cep.split('-');
				if(parseInt(tmp[0]) > 31 || parseInt(tmp[1]) > 12)
				{
				 	$(currentField).val('');
				}
			},
			translation:{
      			'Z': {
					pattern: /[0-9]/
				}
			}
		});
	}

	if($.fn.datepicker)
	{
		setTimeout(function(){

			//Даты ON INVOICE в табличной части
			$('.start_on_invoice_date').datepicker({
				minDate: new Date(),
				dateFormat: "dd-mm-y"
			})
			.change(function() {

				if(this.value.length >= 10)
					return;

				var dateObj = $.datepicker.parseDate( "dd-mm-y", this.value);
				dateObj.setTime(dateObj.getTime() + 86400000);

				var row_n = getJqGridRowNumber(this);
				$('#'+row_n+'_end_date_on_invoice').datepicker( "option", "minDate",  dateObj);
				prepareDate(this);
	        });

			$('.end_on_invoice_date').datepicker({
				minDate: new Date(),
				dateFormat: "dd-mm-y"
			})
			.change(function() {
				prepareDate(this);
			});


			$('.start_action_date').datepicker({
				minDate: new Date(),
				dateFormat: "dd-mm-y"
			})
			.change(function() {

				if(this.value.length >= 10)
					return;

				var dateObj = $.datepicker.parseDate( "dd-mm-y", this.value);
				dateObj.setTime(dateObj.getTime() + 86400000);

				var row_n = getJqGridRowNumber(this);

				$('#'+row_n+'_end_action_date').datepicker( "option", "minDate",  dateObj);
				prepareDate(this);
			});

			$('.end_action_date').datepicker({
				minDate: new Date(),
				dateFormat: "dd-mm-y"
			})
			.change(function() {
				prepareDate(this);
			});
		}, 150);
	}

	$('input[id$=_shopsTitles]').off('copy');
	$('input[id$=_shopsTitles]').on('copy', function(e){

		copied_index = getJqGridRowNumber(this);
	});
	$('input[id$=_shopsTitles]').off('paste');
	$('input[id$=_shopsTitles]').on('paste', function(e){

		let copied = dopData['shops'][copied_index];
		let targetIndex = getJqGridRowNumber(this);
		addDopData(targetIndex, 'shops', copied);
	});

	$('input[id$=_distrTitles]').off('copy');
	$('input[id$=_distrTitles]').on('copy', function(e){
		copied_index = getJqGridRowNumber(this);
	});
	$('input[id$=_distrTitles]').off('paste');
	$('input[id$=_distrTitles]').on('paste', function(e){

		let copied = dopData['distr'][copied_index];
		let targetIndex = getJqGridRowNumber(this);
		addDopData(targetIndex, 'distr', copied);
	});

	$('input[id$=_brendTitles]').off('copy');
	$('input[id$=_brendTitles]').on('copy', function(e){
		copied_index = getJqGridRowNumber(this);
	});
	$('input[id$=_brendTitles]').off('paste');
	$('input[id$=_brendTitles]').on('paste', function(e){

		let copied = dopData['brend'][copied_index];
		let targetIndex = getJqGridRowNumber(this);
		addDopData(targetIndex, 'brend', copied);
	});

	setTimeout(function(){
		$('td > .dialog, input[id$=_shopsTitles], input[id$=_distrTitles]').hover(function(e){

				if($('body').find('> div.file_hint').length > 0)
				{
					$('body').find('> div.file_hint').remove();
				}

				var row_n = getJqGridRowNumber(this);
				if($(this).hasClass('dialog'))
				{
					if($(this).parents('td').find('#'+row_n+'_shopsTitles').length > 0)
					{
						$(this).parents('td').find('#'+row_n+'_shopsTitles').trigger('mouseenter');
					}
					else if($(this).parents('td').find('#'+row_n+'_distrTitles').length > 0)
					{
						$(this).parents('td').find('#'+row_n+'_distrTitles').trigger('mouseenter');
					}
					return;
				}

				if($(this).parents('td').find('#'+row_n+'_shopsTitles').length > 0)
				{
					var tmp = $(this).parents('td').find('#'+row_n+'_shopsTitles').val();
				}
				else if($(this).parents('td').find('#'+row_n+'_distrTitles').length > 0)
				{
					var tmp = $(this).parents('td').find('#'+row_n+'_distrTitles').val();
				}

				if(!tmp || tmp == '')
				{
					return;
				}

				tmp = tmp.split('; ');

				var d = $('<div />');
				d.addClass('file_hint');
				d.html(tmp.join('<br>'));
				d.css('top', $(this).offset().top+23);
				d.css('left', $(this).offset().left);
				d.css('z-index', 100);
				d.show();
				d.hover(function(){
					$('body').find('> div.file_hint').show();
					$('body').find('> div.file_hint').addClass('nodel');
				}, function(){
					$('body').find('> div.file_hint').remove();
				});
				$('body').append(d);
			},
			function()
			{
				if($('body').find('> div.file_hint.nodel').length == 0)
				{
					$('body').find('> div.file_hint').hide();
				}
			}
		);
	}, 200);

	$('input[id$=_skidka_on_invoice], input[id$=_kompensaciya_off_invoice], input[id$=_roznica_old], input[id$=_roznica_new]').on('change', function(){

		var row_n = getJqGridRowNumber(this);
		removeErrorMessage(this);

		var roznica_new = $('#'+row_n+'_roznica_new').val().replace(/ /g, '');
		var roznica_old = $('#'+row_n+'_roznica_old').val().replace(/ /g, '');

		var on_invoice = $('#'+row_n+'_skidka_on_invoice').val().replace(/ /g, '');
		var off_invoice = $('#'+row_n+'_kompensaciya_off_invoice').val().replace(/ /g, '');

		if(parseInt(roznica_new) > parseInt(roznica_old))
		{
			showMessage('error', false, 'Новая розничная цена не должна быть больше старой');
		}

		var itogo = 0;
		// поле итого
		if(roznica_new == 0 || roznica_old == 0)
		{
			if(off_invoice != '' && on_invoice != '')
			{
				let sum = (parseInt(off_invoice) + parseInt(on_invoice));
				if(sum < 100)
					itogo = sum;
				else
					itogo = 0;
			}
			else if(off_invoice != '')
			{
				itogo = off_invoice;
			}
			else if(on_invoice != '')
			{
				itogo = on_invoice;
			}
		}
		else if(roznica_new > 0 && roznica_old > 0)
		{
			itogo = (1 - (parseInt(roznica_new)/parseInt(roznica_old)))*100;
			itogo = itogo.toFixed(1);
		}

		$('#'+row_n+'_skidka_itogo').val(itogo);

		setTimeout(function (){
			illumination($('#'+row_n+'_skidka_itogo'), 0);
		}, 300);
	});

	$("input[id$=_tovsTitles]").hover(function(){

		if($(this).val() == '')
			return;

		var h = $('div.tov_hint');
		if(h.length > 0)
		{
			h.html($(this).val());
			h.css('top', $(this).offset().top + $(this).outerHeight());
			h.css('left', $(this).offset().left);
			h.show();
		}
		else
		{
			var d = $('<div />');
			d.addClass('tov_hint');
			d.css('position', 'absolute');
			d.css('background', '#fff');
			d.css('width', '550px');
			d.css('padding', '5px');
			d.css('z-index', '100');
			d.css('border', '1px solid #ccc');
			d.css('min-height', '27px');
			d.css('height', 'auto');
			d.css('top', $(this).offset().top + $(this).outerHeight());
			d.css('left', $(this).offset().left);
			d.css('display', 'block');
			d.on('click', function(){
				$('.tov_hint').hide();
			});
			d.html($(this).val());
			// $(this).before(d);
			$('body').append(d);
		}
	},
	function(){
		$('.tov_hint').hide();
	});
	let selector = 'input[id$=_shopsTitles], input[id$=_tovsTitles], input[id$=_articulSK], input[id$=_zakup_new], input[id$=_zakup_old], input[id$=_distrTitles], input[id$=_brendTitles], input[id$=_skidka_itogo], .maskDate, input[id$=_kodTov], .selectInRow';

	$(selector).off('change');
	$(selector).on('change', function()
	{
		removeErrorMessage(this);
	});
}
function addDopData(rowNum, field, value)
{
	if(!dopData[field])
	{
		dopData[field] = new Array();
	}
	dopData[field][rowNum] = value;
}
function checkAddField(rowNum, index, value)
{
	if(!checkFields[rowNum])
	{
		checkFields[rowNum] = [];
	}
	checkFields[rowNum][index] = value;
}
function attachDialogBtn(elem, type)
{
	setTimeout(function(){
		$(elem).after('<div class="dialog" style="top:'+
			$(elem).position().top+'px;'
			+'left:'+
			($(elem).position().left + $(elem).outerWidth()-22)
			+'px;height:'+
			$(elem).outerHeight()
			+'px" data-type="'+type+'">...</div>');
	}, 150);
}
function removeErrorMessage(el)
{
	$(el).parents('td').find('.error_message').remove();
}