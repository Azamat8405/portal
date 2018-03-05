$(function(){
	var cache_avtocomplete = {};

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
	}

	$('.field_input_file > .file').click(function(){

		$( "#contragent_dialog" ).dialog({
			closeOnEscape:true
		});
	});

	$('#action_type').selectmenu();

	$("#contragent_title").autocomplete({
		source: function( request, response ) {
			var term = request.term;
			if ( term in cache_avtocomplete )
			{
				response( cache_avtocomplete[ term ] );
				return;
			}
			$.getJSON( "/sys/getContragents", request, function( data, status, xhr ) {
				cache_avtocomplete[term] = data;
				response( data );
			});
		},
		minLength: 2,
		select: function( event, ui ) {
			$('#contragent').val(ui.item.val);
		}
	});

	if($.fn.handsontable && $('#table_data').length > 0)
	{
		// var edit_td = null;
		var InputFileEditor = Handsontable.editors.BaseEditor.prototype.extend();

		InputFileEditor.prototype.init = function(t,y,u) {

			// Create detached node, add CSS class and make sure its not visible
			this.btn = $('<div id="tov_form"><form><input type="hidden" id="tov_id" value="123456"></form></div>');
			this.btn.hide();

			// Attach node to DOM, by appending it to the container holding the table
			this.instance.rootElement.appendChild(this.btn.get(0));
		};

		//заполчняем опции
		InputFileEditor.prototype.prepare = function(row, col, prop, td, originalValue, cellProperties) {
			// Invoke the original method...
			Handsontable.editors.BaseEditor.prototype.prepare.apply(this, arguments);
		};
		InputFileEditor.prototype.setValue = function(value) {

			// $('#tov_id').val(value);
		};

		InputFileEditor.prototype.getValue = function() {
			return $('#tov_id').val();
		};
		InputFileEditor.prototype.open = function(e, r) {

console.log('open');

			//	this.originalValue = this.originalValue+1;
			//	$(this.TD).html(this.originalValue);

			// var el = this;
			// el.originalValue = 777;

			tov_dialog = $('#tov_form').dialog({
				buttons: {
		        	"Выбрать": function() {

						tov_dialog.dialog( "close" );
					}
				},
			});
		};

		// Hides the editor after cell value has been changed.
		InputFileEditor.prototype.close = function(e,u) {

// return false;
console.log('close');

		// tov_id
// console.log(this.originalValue);

// 			$(this.TD).innerHTML('');
//			$(this.TD).find('.testr').hide();

		};

		InputFileEditor.prototype.focus = function() {
console.log('focus');

		};


console.log(Handsontable.dom);


var $$ = function(id) {
      return document.getElementById(id);
    }, save = $$('save');



Handsontable.dom.addEvent(save, 'click', function() {
    // save all cell's data

	console.log('save');

console.log(hot.getData());

    ajax('scripts/json/save.json', 'GET', JSON.stringify({data: hot.getData()}), function (res) {
      var response = JSON.parse(res.response);

      if (response.result === 'ok') {
        exampleConsole.innerText = 'Data saved';
      }
      else {
        exampleConsole.innerText = 'Save error';
      }
    });

});




		var data = [
			["", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda", "Ford", "Tesla", "Toyota", "Honda"],
			["2017", "<div>sfsdf</div>", 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2018", 20, 11, 14, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13],
			["2019", 30, 15, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13, 10, 11, 12, 13]
		];


		var container = document.getElementById('table_data');
		var hot = new Handsontable(container, {
			data:data,
			colWidths: [50,50,50],
			rowHeaders:true,
			colHeaders:true,

			sortIndicator: true,
			columnSorting: {
			    column: 2
			  },

			allowRemoveColumn:false,
			allowRemoveRow:false,
			allowInsertColumn:false,
			allowInsertRow:false,

			colHeaders: [
			    'Товар',
			    'Магазин',
			    'Дистрибьютор',
			    'Тип акции',
			    'Размер скидки ON INVOICE',
			    'Процент компенсации ON INVOICE',
			    'Итого скидка',
			    'Старая закупочная скидка',
			    'Новая закупочная скидка',
			    'Дата начала скидки ON INVOICE',
			    'Дата окончания скидки ON INVOICE',
			    'Старая розничная цена',
			    'Новая розничная цена',
			    'Описание',
			    'Пометки',
			    'Кол-во ????'
			  ],

			columns: [
				{
					type: 'autocomplete',
					allowHtml: true,
					source:['dddd','ssss'],

// 					source: function (query, process){
// console.log(query);
// 						$.ajax({
// 				            url: '/sys/getTovars',
// 				            // dataType: 'json',
// 				            data: {
// 								query: query
// 				            },
// 				            success: function (response) {

// console.log("response", response);

// 				            	//process(JSON.parse(response.data)); // JSON.parse takes string as a argument
// 					            process(response.label);

// 				            }
// 						});
// 					}
				},
				{
					editor:InputFileEditor
				},
			],

			minSpareRows:20,
			manualRowMove: true,
			manualColumnMove: true,
			manualRowResize: true,
			manualColumnResize: true,
			filters:true,
			stretchH: 'all',
		    contextMenu: true,
			height:500,
			width:function(){
				return $('.content').width() - 20;
			},
			afterChange:function (change, source)
			{
				if (source === 'loadData')
					return;
			}
		});

		// hot.cellTypes.registerCellType('input_file', {
		// 	editor: copyablePasswordEditor,
		// 	renderer: copyablePasswordRenderer,
		// 	validator: dsdfs
		// });

		// hot.updateSettings({
		//    	cells: function (row, col, prop) {
		// 		var cellProperties = {};
		//      		if(row == 2 && col == 2)
		//      		{
		//        		cellProperties.readOnly = true;
		// 		}
		// 		return cellProperties;
		// 	}
		// });


		hot.updateSettings({
			cells: function (row, col, prop) {
				// var cellProperties = {};
		  //    		if(row == 2 && col == 2)
		  //    		{
		  //      		cellProperties.readOnly = true;
				// }
				// return cellProperties;
			},
			beforeKeyDown: function (e) {

					// var selection = hot.getSelected();
// console.log(selection);
					// e.stopImmediatePropagation();

					// if (e.keyCode === 8 || e.keyCode === 46) {
					// 	Handsontable.dom.stopImmediatePropagation(e);
					// 	// remove data at cell, shift up
					// 	hot.spliceCol(selection[1], selection[0], 1);
					// 	e.preventDefault();
					// }
				 //  	// ENTER
					// else if (e.keyCode === 13)
					// {
					// 	// if last change affected a single cell and did not change it's values
					// 	if (lastChange && lastChange.length === 1 && lastChange[0][2] == lastChange[0][3]) {
					// 	  Handsontable.dom.stopImmediatePropagation(e);
					// 	  hot.spliceCol(selection[1], selection[0], 0, ''); // add new cell
					// 	  hot.selectCell(selection[0], selection[1]); // select new cell
					// 	}
					// }
					// lastChange = null;

				}
			}
		);

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

	}



	// Handsontable.editors.registerEditor('input_file', InputFile);
	// class function InputFile{
	// }

});