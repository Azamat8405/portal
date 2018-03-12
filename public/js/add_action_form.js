var cache_avtocomplete_contr = {};
var cache_avtocomplete_tovs = {};
var cache_avtocomplete_shops = {};
var cache_shops_dialog = {};
var cache_tovs_categs_dialog = {};
var cache_tovs_dialog = {};

$(function(){

	if($.fn.datepicker)
	{
		$('#start_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		}).change(function() {
			to.datepicker( "option", "minDate",  $.datepicker.parseDate( "dd-mm-yy", this.value ));
        });
		var to = $('#end_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		});

		//Даты ON INVOICE в табличной части
		$('.start_on_invoice_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		}).change(function() {

			var d = $.datepicker.parseDate( "dd-mm-yy", this.value);

			var row_n = getRowNumber(this);
			var end_date = $('input[name^=end_date_on_invoice]:eq('+row_n+')');
			end_date.datepicker( "option", "minDate",  new Date(d.getTime() + 86400000));

			var tmpDate = new Date(d.getTime() + 604800000);
			var m = tmpDate.getMonth()+1;

			end_date.val(tmpDate.getDate()+'-'+(m < 10 ? '0'+m : m)+'-'+tmpDate.getYear());
        });

		$('.end_on_invoice_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		});
	}

	// выбор чекбоксов во всплывающем окне выбра
	$('#shops_dialog, #tovs_dialog').on('click', 'input[type=checkbox]', function(e){
		if($(this).is(':checked'))
			var chck = false;
		else
			var chck = true;

		$(this).next().next().find('input[type=checkbox]').each(function(){
			if(chck)
				$(this).prop('checked',false);
			else
				$(this).prop('checked',true);
		});
	});

	//открываем/скрываем разделы(папки) в окне выбора
	$('#shops_dialog, #tovs_dialog').on('click', ' ul li label', function(e){

		if($(this).next('ul').length > 0)
		{
			e.preventDefault();
		}

		if($(this).next('ul').is(':visible'))
		{
			$(this).next('ul').hide();
		}
		else
		{
			$(this).next('ul').show();
		}
	});

	//подгрузка товаров в раздел. в окне выбора товаров.
	$('#tovs_dialog').on('click', ' ul li  ul li  ul li  ul li label', function(e){

		if($(this).prev('[value]').length > 0)
		{
			return;
		}
		e.preventDefault();

		var ul_el = $(this).next('ul');
		if(ul_el.find('li').length == 0)
		{
			var id_categ = $(this).prev().data('value');
			if(cache_tovs_dialog[id_categ])
			{
				build_tov_list(cache_tovs_dialog[id_categ], ul_el);
			}
			else
			{
				ul_el.append('<img class="load_img" src="/img/load75x75.gif">');
				if(id_categ != '')
				{
					$.ajax({
						url: "/sys/getTovsForCateg/"+id_categ,
						dataType:'json',
						success: function(data){
							cache_tovs_dialog[id_categ] = data;
							build_tov_list(data, ul_el);
						}
					});
				}
			}
		}
	});

	$('.field_input_file > .file').click(function(){

		var el = this;
		if($(this).data('type') == 'getShopsErarhi')
		{
			$("#shops_dialog").dialog({
				title: "Выбор магазинов",
				open:function( event, ui){
					get_shop_list($(el).prev().val().split(','));
				},
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

						$('input[name^=shops]:eq('+row_n+')').val(ids.join());
						$('input.shops:eq('+row_n+')').val(titles.join());

						$("#shops_dialog").dialog("close");
				    }
				}
			});
		}
		else if($(this).data('type') == 'getTovsErarhi')
		{
			$("#tovs_dialog").dialog({
				modal: true,
				title: "Выбор товаров",
				open:function( event, ui )
				{
					let selIds = $(el).prev().val().split(',');

					$("#tovs_dialog").dialog( "option", 'selTovs', selIds);
					get_tovs_categs_list(selIds);
				},
				width:750,
				position: {
					my: "top",
					at: "top",
					of: window
				},
				maxHeight:$(window).height()-50,
				closeOnEscape:true,
				buttons:{
				    "Выбрать товар": function(e){
						var ids=[], titles=[];

						$('#tovs_dialog input[value]:checked').each(function(){
							ids.push($(this).val());
							titles.push($(this).data('title'));
						});
						var row_n = getRowNumber(el);

						$('input[name^=tovs]:eq('+row_n+')').val(ids.join());
						$('input.tovs:eq('+row_n+')').val(titles.join());

						$("#tovs_dialog").dialog("close");
				    }
				}
			});
		}
		else if($(this).data('type') == 'getContagentsErarhi')
		{

			$("#contragent_dialog").dialog({
				title: "Выбор контрагента",
				closeOnEscape:true,
				width:400,
				position:{
					my:"top",
					at:"top",
					of:window
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

						$('input[name^=distr]:eq('+row_n+')').val(ids.join());
						$('input.distr:eq('+row_n+')').val(titles.join());

						// $('#contragent').val(ids.join());
						// $('#contragent_title').val(titles.join());

						$("#contragent_dialog").dialog("close");

					}
				}
			});
		}
	});

	$('.select').selectmenu();

	$("#contragent_title").autocomplete({
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
		select: function( event, ui ) {
			$('#contragent').val(ui.item.val);
		}
	});

	$("input.tovs").autocomplete({
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
			$(this).next().val(ui.item.val);
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
			let arr = $(this).next().val().split(',');
			arr.push(ui.item.val);

			$(this).next().val(arr.join());
		}
	});
});

function build_list(cache_shops_dialog, ids)
{
	$('#shops_dialog').html('');
	$('#shops_dialog').append('<ul>');

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
						display = ' style="display:block;" ';
					}
					else
					{
						city_ch = '';
					}
					li4 += '<li><input type="checkbox" value="'+indexx+'" id="shop'+indexx+'" data-title="'+escape(cache_shops_dialog[ind][inde][index][indexx])+'" '+ch+'>'+
						'<label for="shop'+indexx+'">Магазин '+cache_shops_dialog[ind][inde][index][indexx]+'</label></li>';
				}
				if(city_ch == '')
					reg_ch = '';

				liii += '<li><input type="checkbox" '+city_ch+'>'+
					'<label>'+cache_shops_dialog[ind][inde][index]['title']+'</label><ul '+display+'>'+li4+'</ul></li>';
			}

			if(reg_ch == '')
				macreg = '';

			lii += '<li><input type="checkbox" '+reg_ch+'>'+
				'<label>'+cache_shops_dialog[ind][inde]['title']+'</label><ul '+display+'>'+liii+'</ul></li>';
		}

		li += '<li><input type="checkbox" '+macreg+'>'+
			'<label>'+cache_shops_dialog[ind]['title']+'</label><ul '+display+'>'+lii+'</ul></li>';
	}
	$('#shops_dialog > ul').append(li);
}

function build_tov_list(cache_tovs_dialog, ul_el)
{
	ul_el.html('');

	var parent_checked = ul_el.prev().prev().is(':checked');
	for(ind in cache_tovs_dialog)
	{
		let ch1 = '';
		if($.inArray(cache_tovs_dialog[ind]['c'], $("#tovs_dialog").dialog( "option", 'selTovs')) >= 0 ||
			parent_checked
		)
		{
			ch1 = 'checked="checked"';
			display = ' style="display:block;" ';
		}
		ul_el.append('<li><input type="checkbox" id="tov'+ cache_tovs_dialog[ind]['c'] + '" '+
			' value="' + cache_tovs_dialog[ind]['c'] + '" data-title="'+escape(cache_tovs_dialog[ind]['n'])+'" '+ch1+'>'+
			'<label for="tov'+ cache_tovs_dialog[ind]['c'] + '">'+cache_tovs_dialog[ind]['n']+' '+(cache_tovs_dialog[ind]['art'] ? '('+cache_tovs_dialog[ind]['art']+')' : '')+'</label></li>');
	}
}

function build_tov_categs_list(cache_tovs_categs_dialog, ids)
{
	$('#tovs_dialog').html('');
	$('#tovs_dialog').append('<ul>');

	var li = '';
	for(ind in cache_tovs_categs_dialog)
	{
		var display = '';
		var macreg = '';//checked="checked"
		var lii = '';
		for(inde in cache_tovs_categs_dialog[ind])
		{
			if(!cache_tovs_categs_dialog[ind][inde]['t'])
				continue;

			var reg_ch = '';//checked="checked"
			var liii = '';
			for(index in cache_tovs_categs_dialog[ind][inde])
			{
				if(!cache_tovs_categs_dialog[ind][inde][index]['t'])
					continue;

				var city_ch = '';//checked="checked"
				var li4 = '';
				for(indexx in cache_tovs_categs_dialog[ind][inde][index])
				{
					if(indexx == 't')
						continue;

					var ch = '';//checked="checked"
					var li5 = '';

					if(ch == '')
						city_ch = '';

					li4 += '<li><input type="checkbox" data-value="'+cache_tovs_categs_dialog[ind][inde][index][indexx]['id']+'" '+ch+'>'+
						'<label>'+cache_tovs_categs_dialog[ind][inde][index][indexx]['t']+'</label><ul '+display+'>'+li5+'</ul></li>';
				}
				if(city_ch == '')
					reg_ch = '';

				liii += '<li><input type="checkbox" '+city_ch+'>'+
					'<label>'+cache_tovs_categs_dialog[ind][inde][index]['t']+'</label><ul '+display+'>'+li4+'</ul></li>';
			}

			if(reg_ch == '')
				macreg = '';

			lii += '<li><input type="checkbox" '+reg_ch+'>'+
				'<label>'+cache_tovs_categs_dialog[ind][inde]['t']+'</label><ul '+display+'>'+liii+'</ul></li>';
		}

		li += '<li><input type="checkbox" '+macreg+'>'+
			'<label>'+cache_tovs_categs_dialog[ind]['t']+'</label><ul '+display+'>'+lii+'</ul></li>';
	}
	$('#tovs_dialog > ul').append(li);
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
		li += '<li><input type="checkbox" id="c_'+cache_contragents[ind]['val']+'" value="'+cache_contragents[ind]['val']+'" '+
			ch+
			' data-title="'+escape(cache_contragents[ind]['label'])+'" >'+
			'<label for="c_'+cache_contragents[ind]['val']+'">'+cache_contragents[ind]['label']+' (ИНН:'+cache_contragents[ind]['inn']+')</label></li>';
	}
	root.find(' > ul').append(li);
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
function get_tovs_categs_list(ids)
{
	if($.isEmptyObject(cache_tovs_categs_dialog))
	{
		$.ajax({
			url: "/sys/getTovsCategsErarhi",
			dataType:'json',
			success: function(data){
				cache_tovs_categs_dialog = data;
				build_tov_categs_list(data, ids);
			}
		});
	}
	else
	{
		build_tov_categs_list(cache_tovs_categs_dialog, ids);
	}
}
function get_contragents_list()
{
	$('#contragent_dialog').append('<img class="load_img" src="/img/load75x75.gif">');
	$.ajax({
		url: "/sys/getContragentsErarhi",
		dataType:'json',
		success: function(data){
			build_contragents_list(data);
		}
	});
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