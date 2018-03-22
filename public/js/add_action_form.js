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

	events();

	$('.addProcessForm').keydown(function(e)
 	{
        if(e.keyCode == 13)
        {
			e.preventDefault();
			return false;
        }
	});

	if($('.err_dialog_messages').length > 0)
	{
		let div = $('<div id="">');
		div.attr('id', 'err_dialog_messages');
		$('body').append(div);

		$('#err_dialog_messages').dialog({
			autoOpen: true,
			width:550,
			title:'Внимание!',
			classes: {
				'ui-dialog-titlebar':'error_dialog',
			},
			open:function()
			{
				let html = '';
				$('.err_dialog_messages').each(function(){
					html += $(this).html()+"<br>";
				});
				$('#err_dialog_messages').html(html);
			}
		});
	}

	if($('.ok_dialog_messages').length > 0)
	{
		let div = $('<div id="">');
		div.attr('id', 'ok_dialog_messages');
		$('body').append(div);

		$('#ok_dialog_messages').dialog({
			autoOpen: true,
			width:550,
			title:'Внимание!',
			classes: {
				'ui-dialog-titlebar':'success_dialog',
			},
			open:function()
			{
				let html = '';
				$('.ok_dialog_messages').each(function(){
					html += $(this).html()+"<br>";
				});
				$('#ok_dialog_messages').html(html);
			}
		});
	}

	$('.addProcessForm').submit(function(){
		$('.skidka_itogo').prop('disabled', false);
	});

	var $header = null;
	setTimeout(function(){
		$header = $('#tableHeader');
		var $thead = $('#tableTovs thead');

		$header.css({
		    'width':'230%',
		    'display':'block',
		    'position':'fixed',
		    'background':'#fff',
		    'overflow':'hidden',
		    'z-index':'15',
		});
		$thead.find('th').each(function(index){
			var $newdiv = $('<div />', {
				style: 'width:'+ $(this).outerWidth() + 'px'
	        });
			$newdiv.css({
				'display':'table-cell',
			    'padding':'2px 10px',
			    'border':'1px solid #ccc',
			    'border-collapse':'collapse',
				'border-right':'none',
				'vertical-align': 'middle',
				'line-height':'16px',
				'text-align': 'center'
			});

			if(index == 0)
			{
	        	$newdiv.html($(this).html());
			}
			else
			{
	        	$newdiv.text($(this).text());
			}

	        $header.append($newdiv);
		});
	}, 100);

	$('#tableHeader').on('click', '#delAll', function(){
		if($(this).is(':checked'))
		{
			$('.deleteRow').prop('checked', true);
		}
		else
		{
			$('.deleteRow').prop('checked', false);
		}	
	})


	$.each(selects_cats, function(index, value){
		if(selects_cats[index] == '#tovBrendSelect')
		{
			$(value).select2(select2OptionBrends);
		}
		else
		{
			$(value).select2(select2Option);
			$(value).on('change.select2', function(e){
				var params = [];
				params['id'] = selects_cats[index];
				change_select2_cats(e, this, params);
			});
		}
	});

	$.each(selectsBrens, function(index, value){

		$(value).select2(select2Option);
		$(value).on('change.select2', function(e){

			var params = [];
			params['id'] = selectsBrens[index];
			change_select2_cats(e, this, params);
		});
	});

	var $viewport = $('.table_data'); 
	$viewport.scroll(function(){

		if(!$header)
			return;
		$header.css({
			left: $('#tableTovs').offset().left
		});
	});

	$(window).resize(function(){
		$header.css({
			left: $('#tableTovs').offset().left
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
		var dateObj = $.datepicker.parseDate( "dd-mm-yy", val);
		dateObj.setTime(dateObj.getTime() + 86400000);

		to.datepicker( "option", "minDate",  dateObj);
		$('.start_on_invoice_date').datepicker( "option", "maxDate", val);
	}

	if($.fn.datepicker)
	{
		var from = $('#start_date').datepicker({
			dateFormat: "dd-mm-yy"
		}).change(function() {

			tmp2(this.value);
		});
		var to = $('#end_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		});
	}

	if($("#process_type").length > 0)
	{
		var tmp = function (){
			var d = new Date();
			d.setTime(d.getTime() + $("#process_type option:selected").data('dedlain') * 1000 );
			from.datepicker( "option", "minDate", d);
			return d;
		}
		$('#process_type').select2({
			width:'160',
			minimumResultsForSearch:Infinity
 		});
 		tmp();
 		$("#process_type").on('change', function(evt) {
			tmp();

			if($('#start_date').val() != '')
			{
				tmp2( $('#start_date').val() );
			}
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

		if($(el).data('type') == 'getContagentsErarhi')
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
						$('input.distrTitles:eq('+row_n+')').val(titles.join());

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
			$('#select2-tovCategory-container').parents('.select2-selection').css('border','1px solid red')
			err = true;
		}
		else
		{
			$('#select2-tovCategory-container').parents('.select2-selection').css('border','1px solid #aaa')
			arr.tovCategory = $('#tovCategory').val();
		}

		if($('#tovGroup').val() == 0)
		{
			$('#select2-tovGroup-container').parents('.select2-selection').css('border','1px solid red')
			err = true;
		}
		else
		{
			$('#select2-tovGroup-container').parents('.select2-selection').css('border','1px solid #aaa')
			arr.tovGroup = $('#tovGroup').val();
		}

		if($('#tovTipIsdeliya').val() == 0)
		{
			$('#select2-tovTipIsdeliya-container').parents('.select2-selection').css('border','1px solid red')
			err = true;
		}
		else
		{
			$('#select2-tovTipIsdeliya-container').parents('.select2-selection').css('border','1px solid #aaa')
			arr.tovTipIsdeliya = $('#tovTipIsdeliya').val();
		}

		if($('#tovVidIsdeliya').val() == 0)
		{
			$('#select2-tovVidIsdeliya-container').parents('.select2-selection').css('border','1px solid red')
			err = true;
		}
		else
		{
			$('#select2-tovVidIsdeliya-container').parents('.select2-selection').css('border','1px solid #aaa')
			arr.tovVidIsdeliya = $('#tovVidIsdeliya').val();
		}

		arr.tovBrend = $('#tovBrendSelect').val();
		arr.division = $('#division').val();
		arr.oblast = $('#oblast').val();
		arr.city = $('#city').val();
		arr.shop = $('#shop').val();

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
				if($('.tovs[value='+data.items[ind].c+']').length == 0)
				{
					addRow(data.items[ind], data.shop);
				}
			}
			delRows(true);
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
			'<label for="c_'+cache_contragents[ind]['val']+'">'+cache_contragents[ind]['label']+' (ИНН:'+cache_contragents[ind]['inn']+')</label></li>';
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

function addRow(itemsToFill, shopsToFill, tr)
{
	if($('.select').selectmenu().length)
	{
		$('.select').selectmenu("destroy");
	}
	$('.start_on_invoice_date, .end_on_invoice_date').datepicker("destroy");

	if(!tr)
	{
		var t = $('.table_data table tr:eq(1)').html();
		var tr = $('<tr>');
		tr.append(t);
	}
	else
	{
		tr = $(tr);
	}

	let newRowNumber = ($('.table_data table tr').length - 1)

	tr.find('input,textarea,select').val('');
	tr.find('.error_message').remove();
	tr.find('.start_on_invoice_date, .end_on_invoice_date ').removeAttr('id');
	tr.find('input.row_number').val(newRowNumber);

	if(itemsToFill)
	{
		tr.find('input.tovs').val(itemsToFill['c']);
		tr.find('input.tovsTitles ').val(itemsToFill['n']+' ('+itemsToFill['a']+')');
	}
	if(shopsToFill)
	{
		var ids = [], ttls = [];
		for(ind in shopsToFill)
		{
			ids.push(shopsToFill[ind].id);
			ttls.push(shopsToFill[ind].title);
		}

		tr.find('input[name^=shops]').val( ids.join() );
		tr.find('input.shops').val( ttls.join() );
	}

	$('.table_data table tbody').append(tr);
	events();
	$(window).trigger('resize');
}

function delRows(allEmpty)
{
	if(allEmpty || confirm('Вы хотите удалить выбранные строки?'))
	{
		if(allEmpty)
		{
			$('#tableTovs input.tovs[value=""]').parents('tr').remove();
		}
		else
		{
			if($('#tableTovs input.row_number').length > 1)
			{
				let addEmptyTr = null;
				if($('.deleteRow:checked').length == $('.deleteRow').length)
				{
					addEmptyTr = $('.deleteRow').parents('tr').get(0);
				}
				$('.deleteRow:checked').parents('tr').remove();

				if(addEmptyTr)
				{
					$('.table_data table tbody').append(addEmptyTr);
					addRow(false, false, addEmptyTr);
				}
			}
		}

		$('#tableTovs input.row_number').each(function(index){
			$(this).val(index);
		});
	}
	$('#delAll').prop('checked', false);
	$(window).trigger('resize');
}

function events()
{
	if($.fn.mask)
	{
		$('.maskProcent').mask('ZZZZZ', {
		    placeholder: "0 %",
			onKeyPress: function(cep, event, currentField, options){

				var reg2 = new RegExp("[^\.\,0-9]+");
				var reg = new RegExp("[0-9]{1,3}[(\.|\,)]*[0-9]{0,2}");

				if(parseFloat(cep) > 100 || parseFloat(cep) < 0 || !reg.test(cep) || reg2.test(cep))
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

		$('.maskPrice').mask('ZZZZZZZZZZ', {
		    placeholder: "0 руб",
			onKeyPress: function(cep, event, currentField, options){

				var reg2 = new RegExp("[^\.\,0-9]+");
				var reg = new RegExp("[0-9]{1,6}[(\.|\,)]*[0-9]{0,2}");

				if(parseFloat(cep) < 0 || !reg.test(cep) || reg2.test(cep))
				{
					$(currentField).val('');
				}
			},
			translation:{
      			'Z': {
        			pattern: /[\.\,0-9]*/
      			}
    		}
		})

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
		})
	}

	$('.on_invoice, .off_invoice, .roznica_new, .roznica_old').on('change', function(){

		var row_n = getRowNumber(this);

		var roznica_new = $('input.roznica_new:eq('+row_n+')');
		var roznica_old = $('input.roznica_old:eq('+row_n+')');

		var on_invoice = $('input.on_invoice:eq('+row_n+')');
		var off_invoice = $('input.off_invoice:eq('+row_n+')');

		var itogo = 0;
		if(roznica_new.val() == 0 || roznica_old.val() == 0)
		{
			if(off_invoice.val() != '' && on_invoice.val() != '')
			{
				itogo = (parseInt(off_invoice.val()) + parseInt(on_invoice.val()));
			}
			else if(off_invoice.val() != '')
			{
				itogo = off_invoice.val();
			}
			else if(on_invoice.val() != '')
			{
				itogo = on_invoice.val();
			}
		}
		else if(roznica_new.val() > 0 && roznica_old.val() > 0)
		{
			itogo = (1 - (parseInt(roznica_new.val())/parseInt(roznica_old.val())))*100;
			itogo = itogo.toFixed(1);

// "расчетное поле
// если РЦ старая/новая не заполнено, то (Размер скидки он-инвойс + Размер скидки офф-инвойс)
// если РЦ старая/новая заполнено, то (1 - РЦ новая цена / РЦ старая цена)
// проверка:  или пусто или % (значение от 0 до 1 включительно)"
		}
		$('input.skidka_itogo:eq('+row_n+')').val(itogo);
	});

	if($.fn.datepicker)
	{
		//Даты ON INVOICE в табличной части
		$('.start_on_invoice_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		})
		.change(function() {

			var dateObj = $.datepicker.parseDate( "dd-mm-yy", this.value);
			dateObj.setTime(dateObj.getTime() + 86400000);

			var row_n = getRowNumber(this);
			var end_date = $('input[name^=end_date_on_invoice]:eq('+row_n+')');
			end_date.datepicker( "option", "minDate",  dateObj);
        });

		$('.end_on_invoice_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		});
	}

	//TODO удалить модель selectmenu
	$('.select').selectmenu();

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
		select: function(event, ui) {
			$('input.distr').val(ui.item.val);
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
		select: function( event, ui )
		{
			$(this).parents().find('.tovs').val(ui.item.val);
		}
	});

	$("input.shops").autocomplete({
		source: function( request, response ){
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
			$(this).next().val(ui.item.val);
		}
	});
}

function fillCategsSelect2Filter(id, data)
{
	var select = $(id);
	select.find('option').each(function(index)
	{
		if(index != 0)
		{
			$(this).remove();
		}
	});

	if(id == '#tovGroup')
	{
		$('#tovTipIsdeliya option').each(function(index)
		{
			if(index != 0)
			{
				$(this).remove();
			}
		});
		$('#tovTipIsdeliya').select2(select2Option).trigger('change');
	}

	if(id == '#tovGroup' || id == '#tovTipIsdeliya')
	{
		$('#tovVidIsdeliya option').each(function(index)
		{
			if(index != 0)
			{
				$(this).remove();
			}
		});
		$('#tovVidIsdeliya').select2(select2Option).trigger('change');
	}

	if(id == '#oblast')
	{
		$('#city option').each(function(index)
		{
			if(index != 0)
			{
				$(this).remove();
			}
		});
		$('#city').select2(select2Option).trigger('change');
	}

	if(id == '#oblast' || id == '#city')
	{
		$('#shop option').each(function(index)
		{
			if(index != 0)
			{
				$(this).remove();
			}
		});
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
		if(params['id'] == '#tovVidIsdeliya')
		{
			$.ajax({
				url: "/sys/getBrendsForCategs/"+$(this_el).val(),
				dataType:'json',
				success: function(data){
					cache[$(this_el).val()] = data;
					fillCategsSelect2Filter(selects[ind+1], data);
				}
			});
		}
		else if($.inArray(params['id'], selects_cats) >= 0 && params['id'] != '#tovBrendSelect')
		{
			$.ajax({
				url: "/sys/getSubCategs/"+$(this_el).val(),
				dataType:'json',
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