var cache_avtocomplete_contr = {};
var cache_avtocomplete_tovs = {};
var cache_avtocomplete_shops = {};
var cache_shops_dialog = {};
var cache_tovs_categs_dialog = {};
var cache_contragents_dialog = {}; 
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
		e.preventDefault();

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
		e.preventDefault();

		if($(this).prev('[value]').length > 0)
		{
			return;
		}

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

		if($(this).data('type') == 'getShopsErarhi')
		{
			var el = this;
			$("#shops_dialog").dialog({
				title: "Выбор магазинов",
				open:function( event, ui ) {
					get_shop_list($(el).prev().val().split(','));
				},
				width:500,
				modal: true,
				position: {
					my: "top",
					at: "top",
					of: window
				},
				maxHeight:$(window).height()-50,
				closeOnEscape:true,
				buttons: {
				    "Выбрать магазин": function(e){
						var ids = [];
						$('#shops_dialog input[value]:checked').each(function(){
							ids.push($(this).val());
						});

						var str = ids.join();
						var row_n = getRowNumber(el);

						$('input[name^=shops]:eq('+row_n+')').val(str);
						$("#shops_dialog").dialog("close");
				    }
				}
			});
		}
		else if($(this).data('type') == 'getTovsErarhi')
		{
			var el = this;
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
				buttons: {
				    "Выбрать товар": function(e){
						var ids=[];
						$('#tovs_dialog input[value]:checked').each(function(){
							ids.push($(this).val());
						});

						var str = ids.join();
						var row_n = getRowNumber(el);
						$('input[name^=tovs]:eq('+row_n+')').val(str);
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
				modal: true,
				open:function( event, ui )
				{
					get_contragents_list();
				},
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
			$.getJSON( "/sys/getContragents", request, function( data, status, xhr ) {
				cache_avtocomplete_contr[term] = data;
				response( data );
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

// 	if($.fn.handsontable && $('#table_data').length > 0)
// 	{
// 		// var edit_td = null;
// 		var InputFileEditor = Handsontable.editors.BaseEditor.prototype.extend();

// 		InputFileEditor.prototype.init = function(t,y,u) {

// 			// Create detached node, add CSS class and make sure its not visible
// 			this.btn = $('<div id="tov_form"><form><input type="hidden" id="tov_id" value="123456"></form></div>');
// 			this.btn.hide();

// 			// Attach node to DOM, by appending it to the container holding the table
// 			this.instance.rootElement.appendChild(this.btn.get(0));
// 		};

// 		//заполчняем опции
// 		InputFileEditor.prototype.prepare = function(row, col, prop, td, originalValue, cellProperties) {
// 			// Invoke the original method...
// 			Handsontable.editors.BaseEditor.prototype.prepare.apply(this, arguments);
// 		};
// 		InputFileEditor.prototype.setValue = function(value) {

// 			// $('#tov_id').val(value);
// 		};

// 		InputFileEditor.prototype.getValue = function(e) {

// // console.log(this.getSelected());
// 			return $('#tov_id').val();
// 		};
// 		InputFileEditor.prototype.open = function(e, r) {

// //			console.log('open');

// //			this.originalValue = this.originalValue+1;
// //			$(this.TD).html(this.originalValue);
// // 			console.log(this.originalValue);

// // $('#tov_id').val(this.originalValue);

// 			// var el = this;
// 			// el.originalValue = 777;


// 			tov_dialog = $('#tov_form').dialog({
// 				buttons: {
// 			       	"Выбрать": function() {

// 						// $('#tov_id').val(value);

// 						tov_dialog.dialog( "close" );
// 					}
// 				},
// 			});
// 		};

// 		// Hides the editor after cell value has been changed.
// 		InputFileEditor.prototype.close = function(e,u) {

// // return false;
// console.log('close');

// 		// tov_id
// // console.log(this.originalValue);

// // 			$(this.TD).innerHTML('');
// //			$(this.TD).find('.testr').hide();

// 		};

// 		InputFileEditor.prototype.focus = function() {
// console.log('focus');

// 		};


// console.log(Handsontable.dom);


// var $$ = function(id) {
//       return document.getElementById(id);
//     }, save = $$('save');



// Handsontable.dom.addEvent(save, 'click', function() {
//     // save all cell's data

// 	console.log('save');

// console.log(hot.getData());

//     ajax('scripts/json/save.json', 'GET', JSON.stringify({data: hot.getData()}), function (res) {
//       var response = JSON.parse(res.response);

//       if (response.result === 'ok') {
//         exampleConsole.innerText = 'Data saved';
//       }
//       else {
//         exampleConsole.innerText = 'Save error';
//       }
//     });
// });

// 		var data = [

// 			["", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda"],
// 			["2017", "<div>sfsdf</div>", 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
// 			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
// 			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
// 			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
// 			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
// 			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
// 			["2019", 30, 15, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13]
// 		];


// 		var container = document.getElementById('table_data');
// 		var hot = new Handsontable(container, {
// 			data:data,
// 			colWidths: [50,50,50],
// 			rowHeaders:true,
// 			colHeaders:true,

// 			sortIndicator: true,
// 			columnSorting: {
// 			    column: 2
// 			  },

// 			allowRemoveColumn:false,
// 			allowRemoveRow:false,
// 			allowInsertColumn:false,
// 			allowInsertRow:false,

// 			colHeaders: [
// 			    'Товар',
// 			    'Магазин',
// 			    'Дистрибьютор',
// 			    'Тип акции',
// 			    'Размер скидки ON INVOICE',
// 			    'Процент компенсации ON INVOICE',
// 			    'Итого скидка',
// 			    'Старая закупочная скидка',
// 			    'Новая закупочная скидка',
// 			    'Дата начала скидки ON INVOICE',
// 			    'Дата окончания скидки ON INVOICE',
// 			    'Старая розничная цена',
// 			    'Новая розничная цена',
// 			    'Описание',
// 			    'Пометки',
// 			    'Кол-во ????'
// 			  ],

// 			columns: [
// 				{
// 					type: 'autocomplete',
// 					allowHtml: true,
// 					source:['dddd','ssss'],

// // 					source: function (query, process){
// // console.log(query);
// // 						$.ajax({
// // 				            url: '/sys/getTovarForAvtoComplete',
// // 				            // dataType: 'json',
// // 				            data: {
// // 								query: query
// // 				            },
// // 				            success: function (response) {

// // console.log("response", response);

// // 				            	//process(JSON.parse(response.data)); // JSON.parse takes string as a argument
// // 					            process(response.label);

// // 				            }
// // 						});
// // 					}
// 				},
// 				{
// 					editor:InputFileEditor
// 				},
// 			],

// 			minSpareRows:20,
// 			manualRowMove: true,
// 			manualColumnMove: true,
// 			manualRowResize: true,
// 			manualColumnResize: true,
// 			filters:true,
// 			stretchH: 'all',
// 		    contextMenu: true,
// 			height:500,
// 			width:function(){
// 				return $('.content').width() - 20;
// 			},
// 			afterChange:function (change, source)
// 			{
// 				if (source === 'loadData')
// 					return;
// 			}
// 		});

// 		// hot.cellTypes.registerCellType('input_file', {
// 		// 	editor: copyablePasswordEditor,
// 		// 	renderer: copyablePasswordRenderer,
// 		// 	validator: dsdfs
// 		// });

// 		// hot.updateSettings({
// 		//    	cells: function (row, col, prop) {
// 		// 		var cellProperties = {};
// 		//      		if(row == 2 && col == 2)
// 		//      		{
// 		//        		cellProperties.readOnly = true;
// 		// 		}
// 		// 		return cellProperties;
// 		// 	}
// 		// });


// 		hot.updateSettings({
// 			cells: function (row, col, prop) {
// 				// var cellProperties = {};
// 		  //    		if(row == 2 && col == 2)
// 		  //    		{
// 		  //      		cellProperties.readOnly = true;
// 				// }
// 				// return cellProperties;
// 			},
// 			beforeKeyDown: function (e) {

// 					// var selection = hot.getSelected();
// // console.log(selection);
// 					// e.stopImmediatePropagation();

// 					// if (e.keyCode === 8 || e.keyCode === 46) {
// 					// 	Handsontable.dom.stopImmediatePropagation(e);
// 					// 	// remove data at cell, shift up
// 					// 	hot.spliceCol(selection[1], selection[0], 1);
// 					// 	e.preventDefault();
// 					// }
// 				 //  	// ENTER
// 					// else if (e.keyCode === 13)
// 					// {
// 					// 	// if last change affected a single cell and did not change it's values
// 					// 	if (lastChange && lastChange.length === 1 && lastChange[0][2] == lastChange[0][3]) {
// 					// 	  Handsontable.dom.stopImmediatePropagation(e);
// 					// 	  hot.spliceCol(selection[1], selection[0], 0, ''); // add new cell
// 					// 	  hot.selectCell(selection[0], selection[1]); // select new cell
// 					// 	}
// 					// }
// 					// lastChange = null;

// 				}
// 			}
// 		);

		// $('.htCore td').click(function(){
		// 	console.log('55555');
		// });

		// $('.htCore tr').each(function(i){

		// 	if(i == 0)
		// 		return;
		// 	$exist = false;
		// 	$(this).find('td').each(function() {

		// 		if($(this).html() == 123456789)
		// 		{
		// 			$exist = true;
		// 		}
		// 	});
		// 	if(!$exist)
		// 	{
		// 		$(this).hide();
		// 	}
		// });

		// $('.htCore td').change(function()
		// {
		// 	console.log('777');
		// });
	// }

	// Handsontable.editors.registerEditor('input_file', InputFile);
	// class function InputFile{
	// }
});

function build_list(cache_shops_dialog, ids)
{
	$('#shops_dialog').html('');
	$('#shops_dialog').append('<ul>');

	var li = '';
	for(ind in cache_shops_dialog)
	{
		$display = '';

		$macreg = 'checked="checked"';
		var lii = '';
		for(inde in cache_shops_dialog[ind])
		{
			if(!cache_shops_dialog[ind][inde]['title'])
				continue;

			$reg_ch = 'checked="checked"';
			var liii = '';
			for(index in cache_shops_dialog[ind][inde])
			{
				if(!cache_shops_dialog[ind][inde][index]['title'])
					continue;

				$city_ch = 'checked="checked"';
				var li4 = '';
				for(indexx in cache_shops_dialog[ind][inde][index])
				{
					if(indexx == 'title')
						continue;

					$ch = '';
					if($.inArray(indexx, ids) >= 0)
					{
						$ch = 'checked="checked"';
						$display = ' style="display:block;" ';
					}
					else
					{
						$city_ch = '';
					}

					li4 += '<li><input type="checkbox" value="'+indexx+'" '+$ch+'>'+
					'<label>Магазин '+cache_shops_dialog[ind][inde][index][indexx]+'</label></li>';
				}
				if($city_ch == '')
					$reg_ch = '';

				liii += '<li><input type="checkbox" '+$city_ch+'>'+
					'<label>'+cache_shops_dialog[ind][inde][index]['title']+'</label><ul '+$display+'>'+li4+'</ul></li>';
			}

			if($reg_ch == '')
				$macreg = '';

			lii += '<li><input type="checkbox" '+$reg_ch+'>'+
				'<label>'+cache_shops_dialog[ind][inde]['title']+'</label><ul '+$display+'>'+liii+'</ul></li>';
		}

		li += '<li><input type="checkbox" '+$macreg+'>'+
			'<label>'+cache_shops_dialog[ind]['title']+'</label><ul '+$display+'>'+lii+'</ul></li>';
	}
	$('#shops_dialog > ul').append(li);
}

function build_tov_list(cache_tovs_dialog, ul_el)
{
	ul_el.html('');
	for(ind in cache_tovs_dialog)
	{
		let ch1 = '';
		if($.inArray(cache_tovs_dialog[ind]['c'], $("#tovs_dialog").dialog( "option", 'selTovs')) >= 0)
		{
			ch1 = 'checked="checked"';
			display = ' style="display:block;" ';
		}
		ul_el.append('<li><input type="checkbox" value="' + cache_tovs_dialog[ind]['c'] + '" '+ch1+'>'+
			'<label>'+cache_tovs_dialog[ind]['n']+' '+(cache_tovs_dialog[ind]['art'] ? '('+cache_tovs_dialog[ind]['art']+')' : '')+'</label></li>');
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

					// if(cache_tovs_categs_dialog[ind][inde][index][indexx]['tovs'])
					// {
					// 	var ch1 = '';

					// 	var tovs_els = cache_tovs_categs_dialog[ind][inde][index][indexx]['tovs'];
					// 	for(ind_tov in tovs_els)
					// 	{
					// 		if($.inArray(ind_tov, ids) >= 0)
					// 		{
					// 			ch1 = '';//checked="checked"
					// 			display = ' style="display:block;" ';
					// 		}
					// 		else
					// 		{
					// 			ch = '';
					// 		}
					// 		li5 += '<li><input type="checkbox" value="'+ind_tov+'" '+ch1+'>'+
					// 			'<label>'+tovs_els[ind_tov]['n']+'</label></li>';
					// 	}
					// }

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

function get_contragents_list(ids)
{
	if($.isEmptyObject(cache_contragents_dialog))
	{
		$.ajax({
			url: "/sys/getContragentsErarhi",
			dataType:'json',
			success: function(data){

				cache_contragents_dialog = data;
//				build_tov_categs_list(data, ids);
			}
		});
	}
	else
	{
//		build_tov_categs_list(cache_contragents_dialog, ids);
	}
}

function getRowNumber(el)
{
	return $(el).parents('tr').find('.row_number').val();
}