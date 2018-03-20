var cache_avtocomplete_contr = {};
var cache_avtocomplete_tovs = {};
var cache_avtocomplete_shops = {};
var cache_avtocomplete_brends = {};
var cache_shops_dialog = {};
var cache_shops_regions = {};
var cache_tovs_categs_dialog = {};
var cache_tovs_categs = {};
var cache_tovs_dialog = {};

$(function(){

	events();


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
		$thead.find('th').each(function(){
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
	        $newdiv.text($(this).text());
	        $header.append($newdiv);
		});
	}, 100);

	var selects = ['#tovCategory','#tovGroup','#tovTipIsdeliya','#tovVidIsdeliya', '#tovBrendSelect'];
	$.each(selects, function(index, value){


		$(value).selectmenu({
			open: function( event, ui )
			{
				if(selects[index] == '#tovBrendSelect')
				{
					if($('#tovBrendSelect-button').find('li.ui-menu-item').length <= 1)
					{
						if($('#tovBrendSelect option ').length <= 1)
						{
							$('#tovBrendSelect').selectmenu('destroy');
							$('#tovBrendSelect').hide();

							let inpt =  $('<input />');
							inpt.attr('id', 'tovBrendAvtoComplete');
							$('#tovBrendSelect').after(inpt);
							inpt.button();
							inpt.focus();
						
							$('#tovBrendAvtoComplete').autocomplete(
							{
								source: function( request, response ){
									var term = request.term;
									if ( term in cache_avtocomplete_brends )
									{
										response(cache_avtocomplete_brends[term]);
										return;
									}
									$.getJSON( "/sys/getBrendsForAvtocomplete", request, function( data, status, xhr ) {
										cache_avtocomplete_brends[term] = data;
										response(data);
									});
								},
								minLength: 2,
								select: function( event, ui ) {
									$('#tovBrend').val(ui.item.val);
								}
							});
						}
					}
				}
			},
			change:function(e, ui){
				if(selects[index] == '#tovVidIsdeliya')
				{
					if($('#tovBrendAvtoComplete').length > 0)
					{
						$('#tovBrendAvtoComplete').remove();
						$('#tovBrendSelect').selectmenu({
							open: function( event, ui )
							{
								if($('#tovBrendSelect-button').find('li.ui-menu-item').length <= 1)
								{
									if($('#tovBrendSelect option ').length <= 1)
									{
										$('#tovBrendSelect').selectmenu('destroy');
										$('#tovBrendSelect').hide();

										let inpt =  $('<input />');
										inpt.attr('id', 'tovBrendAvtoComplete');
										$('#tovBrendSelect').after(inpt);
										inpt.button();
										inpt.focus();
									
										$('#tovBrendAvtoComplete').autocomplete(
										{
											source: function( request, response ){
												var term = request.term;
												if ( term in cache_avtocomplete_brends )
												{
													response(cache_avtocomplete_brends[term]);
													return;
												}
												$.getJSON( "/sys/getBrendsForAvtocomplete", request, function( data, status, xhr ) {
													cache_avtocomplete_brends[term] = data;
													response(data);
												});
											},
											minLength: 2,
											select: function( event, ui ) {
												$('#tovBrend').val(ui.item.val);
											}
										});
									}
								}
							},
							change:function(e, ui){

								$('#tovBrend').val(ui.item.value);
								$(selects[index]).find('[value='+ui.item.value+']').prop('selected', true);
							}
						});
					}
				}

				if(!cache_tovs_categs[ui.item.value] || $.isEmptyObject(cache_tovs_categs[ui.item.value]))
				{
					if(selects[index] == '#tovVidIsdeliya')
					{
						$.ajax({
							url: "/sys/getBrendsForCategs/"+ui.item.value,
							dataType:'json',
							success: function(data){

								cache_tovs_categs[ui.item.value] = data;
								fillRegionsFilter(selects[index+1], data);
							}
						});
					}
					else if(selects[index] != '#tovBrendSelect')
					{
						$.ajax({
							url: "/sys/getSubCategs/"+ui.item.value,
							dataType:'json',
							success: function(data){
								cache_tovs_categs[ui.item.value] = data;
								fillCategsFilter(selects[index+1], data);
							}
						});
					}
				}
				else
				{
					fillCategsFilter(selects[index+1], cache_tovs_categs[ui.item.value]);
				}

				if(selects[index] == '#tovBrendSelect')
				{
					$('#tovBrend').val(ui.item.value);
				}
				$(selects[index]).find('[value='+ui.item.value+']').prop('selected', true);
			}
		});
	});

	var selectsBrens = ['#division','#oblast','#city','#shop'];
	$.each(selectsBrens, function(index, value){
		$(value).selectmenu({
			change:function(e, ui){

				if(!cache_shops_regions[ui.item.value] || $.isEmptyObject(cache_shops_regions[ui.item.value]))
				{
					if(selectsBrens[index] == '#city')
					{
						$.ajax({
							url: "/sys/getShopsForRegion/"+ui.item.value,
							dataType:'json',
							success: function(data){
								cache_shops_regions[ui.item.value] = data;
								fillRegionsFilter(selectsBrens[index+1], data);
							}
						});
					}
					else
					{
						$.ajax({
							url: "/sys/getSubRegions/"+ui.item.value,
							dataType:'json',
							success: function(data){
								cache_shops_regions[ui.item.value] = data;
								fillRegionsFilter(selectsBrens[index+1], data);
							}
						});
					}
				}
				else
				{
					fillRegionsFilter(selectsBrens[index+1], cache_shops_regions[ui.item.value]);
				}
				$(selectsBrens[index]).find('[value='+ui.item.value+']').prop('selected', true);
			}
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
	$('#process_type').selectmenu({
		change:function(e, ui){

			var d = new Date();
			d.setTime(d.getTime() + ui.item.element.data('dedlain') * 1000 );
			from.datepicker( "option", "minDate", d);
		}
	});
	if($.fn.datepicker)
	{
		var from = $('#start_date').datepicker({
			dateFormat: "dd-mm-yy"
		}).change(function() {

			var cur_date = new Date();
			var dateObj = $.datepicker.parseDate( "dd-mm-yy", this.value);
			dateObj.setTime(dateObj.getTime() + 86400000);

			to.datepicker( "option", "minDate",  dateObj);
			$('.start_on_invoice_date').datepicker( "option", "maxDate", this.value);
		});
		var to = $('#end_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
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

	//подгрузка товаров в раздел. в окне выбора товаров.
	$('#tovs_dialog').on('click', ' ul li  ul li  ul li  ul li', function(e){
		if($(this).find('[value]').length > 0)
		{
			return;
		}
		e.preventDefault();

		var ul_el = $(this).find(' > ul');
		if(ul_el.find('li').length == 0)
		{
			var id_categ = $(this).find('[data-value]').data('value');
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

	$('*').on('click', '.field_input_file > .file', function(){
		var el = this;
		if($(this).data('type') == 'getShopsErarhi')
		{
			// $("#shops_dialog").dialog({
			// 	title: "Выбор магазинов",
			// 	open:function( event, ui){
			// 		get_shop_list($(el).prev().val().split(','));
			// 	},
			// 	resizable:true,
			// 	width:500,
			// 	modal:true,
			// 	position:{
			// 		my:"top",
			// 		at:"top",
			// 		of:window
			// 	},
			// 	maxHeight:$(window).height()-50,
			// 	closeOnEscape:true,
			// 	buttons:{
			// 	    "Выбрать магазин": function(e){
			// 			var ids=[],titles=[];
			// 			$('#shops_dialog input[value]:checked').each(function(){
			// 				ids.push($(this).val());
			// 				titles.push($(this).data('title'));
			// 			});

			// 			var row_n = getRowNumber(el);

			// 			$('input[name^=shops]:eq('+row_n+')').val(ids.join());
			// 			$('input.shops:eq('+row_n+')').val(titles.join());

			// 			$("#shops_dialog").dialog("close");
			// 	    }
			// 	}
			// });
		}
		else if($(this).data('type') == 'getTovsErarhi')
		{
			// $("#tovs_dialog").dialog({
			// 	modal: true,
			// 	title: "Выбор товаров",
			// 	open:function( event, ui )
			// 	{
			// 		let selIds = $(el).prev().val().split(',');
			// 		let selCategsIds = $(el).prev().prev('input[name^="catsTovs"]').val().split(',');

			// 		$("#tovs_dialog").dialog( "option", 'selTovs', selIds);
			// 		$("#tovs_dialog").dialog( "option", 'selCategs', selCategsIds);

			// 		get_tovs_categs_list();
			// 	},
			// 	resizable:true,
			// 	width:750,
			// 	position: {
			// 		my: "top",
			// 		at: "top",
			// 		of: window
			// 	},
			// 	maxHeight:$(window).height()-50,
			// 	closeOnEscape:true,
			// 	buttons:{
			// 	    "Выбрать товар": function(e){
			// 			var ids=[], titles=[];

			// 			$('#tovs_dialog input[id^=tov]:checked').each(function(){
			// 				ids.push($(this).val());
			// 				titles.push($(this).data('title'));
			// 			});

			// 			//берем все выбранные разделы
			// 			var checked_categs = [];
			// 			$('#tovs_dialog input.tov_categs:checked').each(function(){

			// 				var ul = $(this).next().next();

			// 				//запоминаем раздел только если в нет нет товаров или есть НЕ выбранные товары
			// 				if($(ul).find('input[id^=tov]').length == 0 || $(ul).find('input[id^=tov]').not(':checked').length > 0)
			// 				{
			// 					checked_categs.push($(this).data('value'));
			// 					titles.push($(this).data('title'));
			// 				}
			// 			});

			// 			var row_n = getRowNumber(el);
			// 			$('input[name^=catsTovs]:eq('+row_n+')').val(checked_categs.join());

			// 			$('input.tovs:eq('+row_n+')').val(ids.join());
			// 			$('input.tovsTitles:eq('+row_n+')').val(titles.join());

			// 			$("#tovs_dialog").dialog("close");
			// 	    }
			// 	}
			// });

		}
		else if($(this).data('type') == 'getContagentsErarhi')
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

						$('input[name^=distr]:eq('+row_n+')').val(ids.join());
						$('input.distr:eq('+row_n+')').val(titles.join());

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
			$('#tovCategory-button').css('border','1px solid red');
			err = true;
		}
		else
		{
			$('#tovCategory-button').css('border','1px solid #ccc');
			arr.tovCategory = $('#tovCategory').val();
		}

		if($('#tovGroup').val() == 0)
		{
			$('#tovGroup-button').css('border','1px solid red');
			err = true;
		}
		else
		{
			$('#tovGroup-button').css('border','1px solid #ccc');
			arr.tovGroup = $('#tovGroup').val();
		}

		if($('#tovTipIsdeliya').val() == 0)
		{
			$('#tovTipIsdeliya-button').css('border','1px solid red');
			err = true;
		}
		else
		{
			$('#tovTipIsdeliya-button').css('border','1px solid #ccc');
			arr.tovTipIsdeliya = $('#tovTipIsdeliya').val();
		}

		arr.tovVidIsdeliya = $('#tovVidIsdeliya').val();
		arr.tovBrend = $('#tovBrend').val();

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
				addRow(data.items[ind], data.shop);
			}
			delRows(true);
		}
	});
}

function build_list(cache_shops_dialog, ids)
{
	// $('#shops_dialog').html('');
	// var ul = $('<ul>');

	// ul.addClass("tree");
	// $('#shops_dialog').append(ul);

	// var li = '';
	// for(ind in cache_shops_dialog)
	// {
	// 	var display = '';

	// 	var macreg = 'checked="checked"';
	// 	var lii = '';
	// 	for(inde in cache_shops_dialog[ind])
	// 	{
	// 		if(!cache_shops_dialog[ind][inde]['title'])
	// 			continue;

	// 		reg_ch = 'checked="checked"';
	// 		var liii = '';
	// 		for(index in cache_shops_dialog[ind][inde])
	// 		{
	// 			if(!cache_shops_dialog[ind][inde][index]['title'])
	// 				continue;

	// 			city_ch = 'checked="checked"';
	// 			var li4 = '';
	// 			for(indexx in cache_shops_dialog[ind][inde][index])
	// 			{
	// 				if(indexx == 'title')
	// 					continue;

	// 				ch = '';
	// 				if($.inArray(indexx, ids) >= 0)
	// 				{
	// 					ch = 'checked="checked"';
	// 					display = ' class="active" ';
	// 				}
	// 				else
	// 				{
	// 					city_ch = '';
	// 				}
	// 				li4 += '<li class="no_icon"><input type="checkbox" value="'+indexx+'" id="shop'+indexx+'" data-title="'+escape(cache_shops_dialog[ind][inde][index][indexx])+'" '+ch+'>'+
	// 					'<label for="shop'+indexx+'">Магазин '+cache_shops_dialog[ind][inde][index][indexx]+'</label></li>';
	// 			}
	// 			if(city_ch == '')
	// 				reg_ch = '';

	// 			liii += '<li '+display+'><input type="checkbox" '+city_ch+'>'+
	// 				'<label>'+cache_shops_dialog[ind][inde][index]['title']+'</label><ul>'+li4+'</ul></li>';
	// 		}

	// 		if(reg_ch == '')
	// 			macreg = '';

	// 		lii += '<li '+display+'><input type="checkbox" '+reg_ch+'>'+
	// 			'<label>'+cache_shops_dialog[ind][inde]['title']+'</label><ul>'+liii+'</ul></li>';
	// 	}

	// 	li += '<li '+display+'><input type="checkbox" '+macreg+'>'+
	// 		'<label>'+cache_shops_dialog[ind]['title']+'</label><ul>'+lii+'</ul></li>';
	// }
	// $('#shops_dialog > ul').append(li);
	// $('#shops_dialog').prepend($('<input style="margin:0 3px 8px 9px ;" type="checkbox" id="checkAllShop"><label style="margin:0 0 8px 0;" for="checkAllShop">Выбрать все магазины</label>'));
}

function build_tov_list(cache_tovs, ul_el)
{
	ul_el.html('');

	var parent_checked = ul_el.prev().prev().is(':checked');
	for(ind in cache_tovs)
	{
		let ch1 = '';
		if($.inArray(ind, $("#tovs_dialog").dialog( "option", 'selTovs')) >= 0 ||
			parent_checked
		)
		{
			ch1 = 'checked="checked"';
			display = ' style="display:block;" ';
		}
		ul_el.append('<li class="no_icon"><input type="checkbox" id="tov'+ ind + '" '+
			' value="' + ind + '" data-title="'+escape(cache_tovs[ind]['n'])+'" '+ch1+'>'+
			'<label for="tov'+ ind + '">'+cache_tovs[ind]['n']+' '+(cache_tovs[ind]['art'] ? '('+cache_tovs[ind]['art']+')' : '')+'</label></li>');
	}
}

// function build_tov_categs_list(cache_tovs_categs_dialog)
// {
// 	$('#tovs_dialog').html('');

// 	var ul = $('<ul>');
// 	ul.addClass("tree");
// 	$('#tovs_dialog').append(ul);

// 	var showTovs = [];
// 	var selTovs = $("#tovs_dialog").dialog( "option", 'selTovs');
// 	var selCategs = $("#tovs_dialog").dialog( "option", 'selCategs');

// 	var li = '';
// 	for(ind in cache_tovs_categs_dialog)
// 	{
// 		var display = '';
// 		var macreg = '';
// 		var lii = '';
// 		for(inde in cache_tovs_categs_dialog[ind])
// 		{
// 			if(!cache_tovs_categs_dialog[ind][inde]['t'])
// 				continue;
// 			var reg_ch = 0;
// 			var liii = '';
// 			for(index in cache_tovs_categs_dialog[ind][inde])
// 			{
// 				if(!cache_tovs_categs_dialog[ind][inde][index]['t'])
// 					continue;
// 				var city_ch = 0;
// 				var li4 = '';
// 				for(indexx in cache_tovs_categs_dialog[ind][inde][index])
// 				{
// 					if(indexx == 't')
// 						continue;

// 					var ch = 0;
// 					if($.inArray(indexx, selCategs) >= 0)
// 					{
// 						ch = 1;
// 					}
// 					for(tovId in cache_tovs_dialog[indexx])
// 					{
// 						if($.inArray(tovId, selTovs) >= 0)
// 						{
// 							display = ' class="active" ';
// 							showTovs[indexx] = 1;
// 							if(ch != 2)
// 								ch = 1;
// 						}
// 						else
// 						{
// 							ch = 2;
// 							break;
// 						}
// 					}
// 					if(ch == 1)
// 					{
// 						if(city_ch != 2)
// 							city_ch = 1;
// 					}
// 					else
// 						city_ch = 2;

// 					var title = cache_tovs_categs_dialog[ind][inde][index][indexx]['t']
// 					li4 += '<li '+display+'><input data-title="'+escape(title)+'" class="tov_categs" type="checkbox" data-level="4" data-value="' + indexx + '" '+(ch == 1 ? ' checked="checked" ' : '')+'>'+
// 						'<label>'+title+'</label><ul ></ul></li>';
// 				}
// 				if(city_ch == 1)
// 				{
// 					if(reg_ch != 2)
// 						reg_ch = 1;
// 				}
// 				else
// 					reg_ch = 2;

// 				var title = cache_tovs_categs_dialog[ind][inde][index]['t'];
// 				liii += '<li '+display+'><input data-title="'+escape(title)+'" class="tov_categs" type="checkbox" data-value="'+index+'" '+( city_ch == 1 ? ' checked=""checked' : '')+'>'+
// 					'<label>'+title+'</label><ul>'+li4+'</ul></li>';
// 			}
// 			if(reg_ch == 1)
// 			{
// 				if(macreg != 2)
// 					macreg = 1;
// 			}
// 			else
// 				macreg = 2;

// 			var title = cache_tovs_categs_dialog[ind][inde]['t'];

// 			lii += '<li '+display+'><input data-title="'+escape(title)+'" class="tov_categs" type="checkbox" data-value="'+inde+'" '+( reg_ch == 1 ? ' checked=""checked' : '') + '>' +
// 				'<label>'+title+'</label><ul>'+liii+'</ul></li>';
// 		}

// 		var title = cache_tovs_categs_dialog[ind]['t'];
// 		li += '<li '+display+'><input data-title="'+escape(title)+'" class="tov_categs" type="checkbox" data-value="'+ind+'" '+ ( macreg == 1 ? ' checked=""checked' : '') +'>'+
// 			'<label>'+title+'</label><ul>'+lii+'</ul></li>';
// 	}
// 	$('#tovs_dialog > ul').append(li);
// 	for (ind in showTovs)
// 	{
// 		var ul_el = $('input[data-value='+ind+']').next().next();
// 		build_tov_list(cache_tovs_dialog[ind], ul_el);
// 	}
// }

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
function get_tovs_categs_list()
{
	// if($.isEmptyObject(cache_tovs_categs_dialog))
	// {
	// 	$.ajax({
	// 		url: "/sys/getTovsCategsErarhi",
	// 		dataType:'json',
	// 		success: function(data){
	// 			cache_tovs_categs_dialog = data;
	// 			build_tov_categs_list(data);
	// 		}
	// 	});
	// }
	// else
	// {
	// 	build_tov_categs_list(cache_tovs_categs_dialog);
	// }
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

function addRow(itemsToFill, shopsToFill)
{
	$('.select').selectmenu("destroy");
	$('.start_on_invoice_date, .end_on_invoice_date').datepicker( "destroy" );

	var t = $('.table_data table tr:eq(1)').html();
	let newRowNumber = ($('.table_data table tr').length - 1)

	var tr = $('<tr>');
	tr.append(t);
	tr.find('input,textarea,select').val('');
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

	var t = $('.table_data table tbody').append(tr);
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
			$('.deleteRow:checked').parents('tr').remove();
		}

		$('#tableTovs input.row_number').each(function(index){
			$(this).val(index);
		});
	}
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

				// var reg2 = new RegExp("[^\.\,0-9]+");
				// var reg = new RegExp("[0-9]{1,6}[(\.|\,)]*[0-9]{0,2}");

				// if(parseFloat(cep) < 0 || !reg.test(cep) || reg2.test(cep))
				// {
				// 	$(currentField).val('');
				// }
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

	$('.select').selectmenu();

	$("input.distr").autocomplete({
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
			$(this).next().next().val(ui.item.val);
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

function fillCategsFilter(id, data)
{
	var select = $(id);
	select.find('option').each(function(index)
	{
		if(index != 0)
		{
			$(this).remove();
		}
	});
	select.selectmenu('refresh');

	if(id == '#tovGroup')
	{
		$('#tovTipIsdeliya option').each(function(index)
		{
			if(index != 0)
			{
				$(this).remove();
			}
		});
		$('#tovTipIsdeliya').selectmenu('refresh');
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
		$('#tovVidIsdeliya').selectmenu('refresh');
	}

	for(var i = data.length - 1; i >= 0; i--)
	{
		let op = $('<option />');
		op.text(data[i].title);
		op.attr('value', data[i].id);
		select.append(op);
	}
	select.selectmenu('refresh');
}

function fillRegionsFilter(id, data)
{
	var select = $(id);
	select.find('option').each(function(index)
	{
		if(index != 0)
		{
			$(this).remove();
		}
	});
	select.selectmenu('refresh');

	if(id == '#oblast')
	{
		$('#city option').each(function(index)
		{
			if(index != 0)
			{
				$(this).remove();
			}
		});
		$('#city').selectmenu('refresh');
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
		$('#shop').selectmenu('refresh');
	}

	for(var i = data.length - 1; i >= 0; i--)
	{
		let op = $('<option />');
		op.text(data[i].title);
		op.attr('value', data[i].id);
		select.append(op);
	}
	select.selectmenu('refresh');
}