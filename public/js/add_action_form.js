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
var selects_cats = ['#tovCategory','#tovGroup','#tovTipIsdeliya','#tovVidIsdeliya', '#tovBrendSelect'];
var selectsBrens = ['#division','#oblast','#city','#shop'];
var select2Option = {
	width:'180',
	minimumResultsForSearch:Infinity,
};

var copied_shops_index = null;
var select2OptionBrends = {
	width:'180',
	ajax:{
		delay: 300,
	    url: "/sys/getBrendsForAvtocomplete",
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
	$('body').on('mouseover', '.select2-results__options li', function(){


		tmp = $(this).attr('id').split('-');
		index = parseInt(tmp.length)-1;

		var descr = $('.select option[value='+tmp[index]+']').data('descr');
		if(typeof(descr) != 'undefined')
		{
			var d = $('<div />');
			d.css('position', 'absolute');
			d.addClass('type_descr');

			d.css('top', $(this).offset().top);
			d.css('left', $(this).offset().left-150);
			d.css('width', '150');
			d.css('height', 'auto');
			d.css('min-height', '100');
			d.css('background', '#fff');
			d.css('padding', '7px');
			d.css('z-index', '100');
			d.css('font-size', '12px');
			d.css('line-height', '14px');
			d.css('border', '1px solid #ccc');
			d.html(descr);

			$('body').append(d);
		}

	});
	$('body').on('mouseout', '.select2-results__options li', function(){
		$('.type_descr').remove();
	});

	events();
	$('.addProcessForm').keydown(function(e)
	{
		if(e.keyCode == 13)
		{
			e.preventDefault();
			return false;
        }
	});

	$('.addProcessForm').submit(function(){
		$('.skidka_itogo').prop('disabled', false);
	});

	// var $header = null;

	// setTimeout(function(){

	// 	$parHeader = $('#parentTableHeader');
	// 	$parHeader.css({
	// 	    'width':'82%',
	// 	    'display':'block',
	// 	    'position':'fixed',
	// 	    'overflow':'hidden',
	// 		'z-index':'95',
	// 	});

	// 	$header = $('#tableHeader');
	// 	$header.css({
	// 	    'background':'#fff',
	// 		'width':'1000%',
	// 		'position': 'relative',
	// 	});

	// 	var $thead = $('#tableTovs thead');
	// 	$thead.find('th').each(function(index){

	// 		var $newdiv = $('<div />', {
	// 			style: 'width:'+ $(this).outerWidth() + 'px'
	// 		});
	// 		$newdiv.css({
	// 			'display':'table-cell',
	// 		    'padding':'2px 10px',
	// 		    'border':'1px solid #ccc',
	// 		    'border-collapse':'collapse',
	// 			'border-right':'none',
	// 			'vertical-align': 'middle',
	// 			'line-height':'16px',
	// 			'text-align': 'center'
	// 		});

	// 		if(index == 0)
	// 		{
	//         	$newdiv.html($(this).html());
	// 		}
	// 		else
	// 		{
	//         	$newdiv.text($(this).text());
	// 		}
	//         $header.append($newdiv);
	// 	});
	// }, 100);

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

	// $(window).scroll(function(){

	// 	$('#tableHeader').css({
	// 		top:($(window).scrollTop()*-1)
	// 	});

	// 	$('#parentTableHeader').css({
	// 		top:$('#parentTableHeader').offset().top + ( $(window).scrollTop()*-1)
	// 	});
	// });

	// var $viewport = $('.table_data_block');
	// $viewport.scroll(function(){

	// 	if(!$header)
	// 		return;

	// 	$header.css({
	// 		left: ($('#tableTovs').offset().left - $('#offset').offset().left)
	// 	});
	// });

	// resizeTable();
	// $(window).resize(function(){
	// 	resizeTable();
	// 	$header.css({
	// 		left: ($('#tableTovs').offset().left - $('#offset').offset().left)
	// 	});
	// });

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
		// var tmp = function (){
		// 	var d = new Date();
		// 	d.setTime(d.getTime() + $("#process_type option:selected").data('dedlain') * 1000 );
		// 	from.datepicker( "option", "minDate", d);
		// 	return d;
		// }
		$('#process_type').select2({
			width:'160',
			minimumResultsForSearch:Infinity
 		});
 		// tmp();
 		$("#process_type").on('change', function(evt) {
			$(this).parents('.form-field-input').find('label').removeClass('error_input');

			// tmp();
			// if($('#start_date').val() != '')
			// {
			// 	tmp2( $('#start_date').val() );
			// }
		});
	}


	// выбор чекбоксов во всплывающем окне выбора
	$('#shops_dialog, #tovs_dialog').on('click', 'input[type=checkbox]', function(e){
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
	$('#shops_dialog, #tovs_dialog').on('click', ' ul li', function(e){
	// $('#shops_dialog, #tovs_dialog').on('click', ' ul li label', function(e){

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

	$('div').on('click', '.field_input_file > .file', function(){

		var el = this;

		if($(el).parents('.disabledField').length > 0)
		{
			return;
		}

		if($(this).data('type') == 'getShopsErarhi')
		{
			$("#shops_dialog").dialog({
				title: "Выбор магазинов",
				open:function( event, ui){
					get_shop_list($(el).prev().val().split(','));
				},
				resizable:true,
				width:500,
				modal:true,
				position:{
					my:"top",
					at:"top",
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

						var row_n = getRowNumber(el);

						$(el).parents('.field_input_file').find('input.shops').val(ids.join());
						$(el).parents('.field_input_file').find('input.shopsTitles').val(titles.join(';  '));
						$(el).parents('.field_input_file').find('input.chShop').val(titles.join(';  '));
						$(el).parents('.field_input_file').find('input.shopsTitles').trigger('change');

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
				position:{
 					using:function(t,y){
						var el = $(y.element.element).find('> div').get(0);
						$(el).parents().css('top', 60);
						$(el).parents().css('left', '35%');
					}
				},
				maxHeight:$(window).height()-50,
				close:function(){
					$("#contragent_dialog").html('');
				},
				open:function( event, ui )
				{
					let selIds = $(el).prev().val().split(',');

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

						var row_n = getRowNumber(el);

						$('input.distr:eq('+row_n+')').val(ids.join());
						$('input.distrTitles:eq('+row_n+')').val(titles.join(';  '));
						$('input.chDistr:eq('+row_n+')').val(titles.join(';  '));

						$("#contragent_dialog").dialog("close");
					}
				}
			});
		}
	});

	$('#fillTable').click(function(){
		var err = false;
		var arr = {};

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

		// if($('#tovVidIsdeliya').val() == 0)
		// {
		// 	$('#select2-tovVidIsdeliya-container').parents('.select2-selection').addClass('error_input');
		// 	err = true;
		// }
		// else
		// {
			$('#select2-tovVidIsdeliya-container').parents('.select2-selection').removeClass('error_input');
			arr.tovVidIsdeliya = $('#tovVidIsdeliya').val();
		// }

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
			getTovsToFillTable(arr, 0);
	});
});

function getTovsToFillTable(arr, page)
{
	arr.page = page;
	$.ajax({
		timeout:0,
		url:"/sys/getTovsToFillTable",
		data:arr,
		dataType:'json',
		success: function(data){

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
				if($('.kodTov[value='+data.items[ind].c+']').length == 0)
				{
					addRow(data.items[ind], data.shop);
				}
			}

			delRows(true);
			events();
			$(window).trigger('resize');
		}
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
	$('#contragent_dialog').append('<img class="load_img" src="/img/load75x75.gif">');

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

function getRowNumber(el)
{
	return $(el).parents('tr').find('.row_number').val();
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

function addEmptyRow()
{
	addRow();
	events();
	$(window).trigger('resize');
}

function addRow(itemsToFill, shopsToFill, tr)
{
	$('.select').each(function(){
		if($(this).hasClass("select2-hidden-accessible"))
		{
			$(this).select2('destroy');
			$(this).removeAttr('data-select2-id').find('option').removeAttr('data-select2-id');
		}
	});

	$('.start_on_invoice_date, .end_on_invoice_date').removeClass("hasDatepicker")
	$('.start_on_invoice_date, .end_on_invoice_date').datepicker("destroy");
	if(!tr)
	{
		tr = $('.table_data_block table tr:eq(1)').clone();
	}
	else
	{
		tr = $(tr);
	}

	$('.select').each(function(){

		if($(this).val() == 0)
		{
			$(this).val("0").trigger("change");
		}
	});

	let newRowNumber = ($('.table_data_block table tr').length - 1);
	tr.find('input,textarea').val('');
	tr.find('.error_message').remove();
	tr.find('.start_on_invoice_date, .end_on_invoice_date ').removeAttr('id');
	tr.find('input.row_number').val(newRowNumber);

	if(itemsToFill)
	{
		tr.find('input.kodTov').val(itemsToFill['c']);
		tr.find('input.chKod').val(itemsToFill['c']);

		tr.find('input.tovsTitles').val( itemsToFill['n'] );
		tr.find('.chTitle').val( itemsToFill['n'] );
	}
	if(shopsToFill)
	{
		var ids = [], ttls = [];
		for(ind in shopsToFill)
		{
			ids.push(shopsToFill[ind].id);
			ttls.push(shopsToFill[ind].title);
		}

		tr.find('input.shops').val( ids.join() );
		tr.find('input.shopsTitles').val( ttls.join(';  ') );
		tr.find('input.chShop').val( ttls.join(';  ') );
	}

	$('.table_data_block table tbody').append(tr);
}

function delRows(allEmpty)
{
	$('.select').each(function(){
		if($(this).hasClass("select2-hidden-accessible"))
		{
			$(this).select2('destroy');
			$(this).removeAttr('data-select2-id').find('option').removeAttr('data-select2-id');
		}
	});

	if($('.deleteRow:checked').length > 0 && confirm('Вы хотите удалить выбранные строки?') || allEmpty )
	{
		if(allEmpty)
		{
			$('#tableTovs input.kodTov').each(function(){

				if($(this).val() == '')
				{
					$(this).parents('tr').remove();
				}
			});
		}
		else
		{
			if($('#tableTovs input.row_number').length > 1)
			{
				let addEmptyTr = null;

				if($('.deleteRow:checked').length == $('.deleteRow').length)
				{
					addEmptyTr = $('.deleteRow').parents('tr:eq(0)').clone();
				}
				$('.deleteRow:checked').parents('tr').remove();

				if(addEmptyTr)
				{
					addRow(false, false, addEmptyTr);

					events();
					$(window).trigger('resize');
				}
			}
			else if($('#tableTovs input.row_number').length == 1)
			{
				$('#tableTovs input[type=text], #tableTovs input[type=hidden], #tableTovs select, #tableTovs textarea').val('');

				$('.select').select2(select2Option);
				$('.select').val("0").trigger("change");
			}
		}

		$('#tableTovs input.row_number').each(function(index){
			$(this).val(index);
		});
	}
	$('#delAll, .deleteRow').prop('checked', false);
	$(window).trigger('resize');
}

function events()
{
	$('.shopsTitles, .tovsTitles, .distrTitles, .maskProcent, .maskPrice, .maskDate, .kodTov, .select').off('change');
	$('.shopsTitles, .tovsTitles, .distrTitles, .maskProcent, .maskPrice, .maskDate, .kodTov, .select').on('change', function()
	{
		$(this).parents('td').find('.error_message').remove();
	});

	$('.shopsTitles').off('copy');
	$('.shopsTitles').on('copy', function(e){
		copied_shops_index = getRowNumber(this);
	});
	$('.shopsTitles').off('paste');
	$('.shopsTitles').on('paste', function(e){
		let copied = $('.shops:eq(' + copied_shops_index + ')').val();
		$('.shops:eq(' + getRowNumber(this) + ')').val(copied);
	});

	$('.distrTitles').off('copy');
	$('.distrTitles').on('copy', function(e){
		copied_shops_index = getRowNumber(this);
	});
	$('.distrTitles').off('paste');
	$('.distrTitles').on('paste', function(e){
		let copied = $('.distr:eq(' + copied_shops_index + ')').val();
		$('.distr:eq(' + getRowNumber(this) + ')').val(copied);
	});


	$('.field_input_file > .file, .field_input_file > .shopsTitles, .field_input_file > .distrTitles').off("mouseenter mouseleave");
	$('.field_input_file > .file, .field_input_file > .shopsTitles, .field_input_file > .distrTitles').hover(function(e){

			if($('body').find('> div.file_hint').length > 0)
			{
				$('body').find('> div.file_hint').remove();
			}

			var row_n = getRowNumber(this);

			if($(this).hasClass('file'))
			{
				if($(this).parents('.field_input_file').find('.shopsTitles').length > 0)
				{
					$(this).parents('.field_input_file').find('.shopsTitles').trigger('mouseenter');
				}
				else if($(this).parents('.field_input_file').find('.distrTitles').length > 0)
				{
					$(this).parents('.field_input_file').find('.distrTitles').trigger('mouseenter');
				}
				return;
			}

			var tmp = $(this).parents('.field_input_file').find('.shopsTitles').val();
			if(!tmp || tmp == '')
			{
				tmp = $(this).parents('.field_input_file').find('.distrTitles').val();
				if(!tmp || tmp == '')
				{
					return;
				}
			}
			var d = $(this).find('> div');
			if(d.length == 0)
			{
				var d = $('<div />');
				d.addClass('file_hint');
			}

			tmp = tmp.split('; ');
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

			if( reg.test(str) )
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

	$('.on_invoice, .off_invoice, .roznica_new, .roznica_old').on('change', function(){

		var row_n = getRowNumber(this);

		var roznica_new = $('input.roznica_new:eq('+row_n+')').val().replace(/ /g, '');;
		var roznica_old = $('input.roznica_old:eq('+row_n+')').val().replace(/ /g, '');;

		var on_invoice = $('input.on_invoice:eq('+row_n+')').val().replace(/ /g, '');;
		var off_invoice = $('input.off_invoice:eq('+row_n+')').val().replace(/ /g, '');;

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

// "расчетное поле
// если РЦ старая/новая не заполнено, то (Размер скидки он-инвойс + Размер скидки офф-инвойс)
// если РЦ старая/новая заполнено, то (1 - РЦ новая цена / РЦ старая цена)
// проверка:  или пусто или % (значение от 0 до 1 включительно)"

		}
		$('input.skidka_itogo:eq('+row_n+')').val(itogo);

		setTimeout(function (){
			illumination($('input.skidka_itogo:eq('+row_n+')'), 0);
		}, 300);
	});

	if($.fn.datepicker)
	{
		//Даты ON INVOICE в табличной части
		$('.start_on_invoice_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-y"
		})
		.change(function() {

			var dateObj = $.datepicker.parseDate( "dd-mm-y", this.value);
			dateObj.setTime(dateObj.getTime() + 86400000);

			var row_n = getRowNumber(this);
			var end_date = $('input[name^=end_date_on_invoice]:eq('+row_n+')');
			end_date.datepicker( "option", "minDate",  dateObj);

			prepareDate(this);
        });

		$('.end_on_invoice_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-y"
		})
		.change(function() {
			prepareDate(this);
		});
	}

	$('.select').select2(select2Option).change(function(){
		$('.type_descr').remove();
	});

	$("input.distrTitles").autocomplete({
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
			var n = getRowNumber(this);
			if($(this).val() != $("input.chDistr:eq("+n+")").val())
			{
				$(this).val($("input.chDistr:eq("+n+")").val());
			}
			$("input.distrTitles:eq("+n+")").trigger('change');
		},
		select: function(event, ui) {

			var n = getRowNumber(this);

			$('input.distr:eq('+n+')').val(ui.item.val);
			$('input.chDistr:eq('+n+')').val(ui.item.label);
		}
	});

	$("input.tovsTitles").hover(function(){

			if($(this).val() == '')
				return;

			if($('div.tov_hint').length > 0)
			{
				$('div.tov_hint').html($(this).val());
				$('div.tov_hint').show();
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
				d.on('click', function(){
					$('.tov_hint').hide();
				});
				d.html($(this).val());
				$(this).before(d);
			}
		},
		function(){
			$('.tov_hint').hide();
		});

	$('input.kodTov').autocomplete({
		source: function( request, response ){
			var term = request.term;
			request.kod = true;

			if ( term in cache_avtocomplete_tovs )
			{
				response( cache_avtocomplete_tovs[term]);
				return;
			}
			$.getJSON( "/sys/getTovarForAvtoComplete", request, function( data, status, xhr ) {
				cache_avtocomplete_tovs[term] = data;
				response(data);
			});
		},
		minLength:2,
		change:function()
		{
			var n = getRowNumber(this);
			if($(this).val() != $("input.chKod:eq("+n+")").val())
			{
				$(this).val($("input.chKod:eq("+n+")").val());
			}
			$("input.tovsTitles:eq("+n+")").trigger('change');
		},
		select: function( event, ui )
		{
			var n = getRowNumber(this);

			$(this).val(ui.item.value);

			$('input.chKod:eq('+n+')').val(ui.item.value);
			$('.tovsTitles:eq('+n+')').val(ui.item.val);
			$('.chTitle:eq('+n+')').val(ui.item.val);
		}
	});

	$("input.tovsTitles").autocomplete({
		source: function( request, response ){
			var term = request.term;
			if ( term in cache_avtocomplete_tovs )
			{
				response( cache_avtocomplete_tovs[term]);
				return;
			}
			$.getJSON( "/sys/getTovarForAvtoComplete", request, function( data, status, xhr ) {
				cache_avtocomplete_tovs[term] = data;
				response(data);
			});
		},
		minLength: 2,
		change:function()
		{
			var n = getRowNumber(this);
			if($(this).val() != $("input.chTitle:eq("+n+")").val())
			{
				$(this).val($("input.chTitle:eq("+n+")").val());
			}
			$("input.kodTov:eq("+n+")").trigger('change');
		},
		select: function( event, ui )
		{
			var n = getRowNumber(this);

			$("input.chTitle:eq("+n+")").val(ui.item.label);
			$('.kodTov:eq('+n+')').val(ui.item.val);
			$('input.chKod:eq('+n+')').val(ui.item.val);
		}
	});

	$("input.shopsTitles").autocomplete({
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
			$.getJSON( "/sys/getShops", request, function( data, status, xhr ) {
				cache_avtocomplete_shops[term] = data;
				response(data);
			});
		},
		minLength: 2,
		select: function( event, ui )
		{
			var n = getRowNumber(this);

			$("#tableTovs input.shops:eq("+n+")").val(ui.item.value);
			$("#tableTovs input.chShop:eq("+n+")").val(ui.item.value);
			$("#tableTovs input.shops:eq("+n+")").val(ui.item.val);
		},
		change: function( event, ui )
		{
			var n = getRowNumber(this);
			if($(this).val() != $("#tableTovs input.chShop:eq("+n+")").val())
			{
				$(this).val($("#tableTovs input.chShop:eq("+n+")").val());
			}
		}
	});
}

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
			$.ajax({
				url: "/sys/getBrendsForCategs/"+$(this_el).val(),
				dataType:'json',
				success: function(data){
					cache[$(this_el).val()] = data;
					fillCategsSelect2Filter(selects[4], data);
				}
			});
		}

		if($.inArray(params['id'], selects_cats) >= 0 && params['id'] != '#tovBrendSelect')
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