var cache_kodNomenkatur = {};

var kodNomenkaturAvtocomplete = {
	source: function( request, response ){

		var el = this.bindings[0];
		var n_row = getRowNumber(el);
		var term = request.term;

		if(term in cache_kodNomenkatur)
		{
			$('.tovName:eq('+n_row+')').val(cache_kodNomenkatur[term][0].label);
			return;
		}
		$.ajax({
			url:"/tovs/ajaxGetTovForAvtocomplete",
			data:request,
			dataType:'json',
			success:function(data)
			{
				$(el).removeClass('error_input');
				cache_kodNomenkatur[term] = data;

				$('.tovName:eq('+n_row+')').val(data[0].label);
			},
			error:function(){
				$(el).addClass('error_input');
				$('.tovName:eq('+n_row+')').val('');
				showMessage('error', false, 'Не найден товар с указанным кодом в разделе "Детское питание".');
			}
		});
	},
	minLength: 2,
}

var select2Common = {
	width:'95%',
	minimumResultsForSearch:Infinity,
};

$(function(){
	$('.select_shop').select2({
		width:'350px',
		minimumResultsForSearch:Infinity,
	});
	$('.select_shop').on('change', function(){

		if($(this).val() > 0)
		{
			$(this).parents('.form-field-input').find('.error_input').removeClass('error_input');
		}
	});

	events();
});

function getRowNumber(el)
{
	return $(el).parents('tr.row_number').data('row-number');
}

/**
* params['clear']
* params['remove']
*/
function addRow(tbl, tr, params)
{
	var tr = $(tr).clone();
	if(params['clear'])
	{
		for(ind in params['clear'])
		{
			tr.find(params['clear'][ind]).val('');
		}
	}

	if(params['remove'])
	{
		for(ind in params['remove'])
		{
			tr.find(params['remove'][ind]).remove();
		}
	}
	$(tr).find('.error_input').removeClass('error_input');
	$(tr).find('.select2-hidden-accessible').removeClass('select2-hidden-accessible');
	$(tr).find('option').removeAttr('data-select2-id');
	$(tr).find('*[id]').removeAttr('id');

	let newRowNumber = ($(tbl).find('tr').length - 1);
	tr.data('row-number', newRowNumber);

	$(tbl).append(tr);

	events();
	$(window).trigger('resize');
}

function events()
{
	$('.kodNomenkatur').autocomplete(kodNomenkaturAvtocomplete);
	$('.select').each(function(i, item){

		$(item).removeAttr('data-select2-id');
		if ($(item).hasClass("select2-hidden-accessible"))
		{
			$(item).select2('destroy');
		}
	});

	$('.select').select2(select2Common);

	$('.date').removeClass("hasDatepicker");
	$('.date').datepicker("destroy");
	var d = new Date();
	d.setTime(d.getTime() + 86400000);
	$('.date').datepicker({
		minDate: d
	});
}

function addNewRow()
{
	var params = [];

	params['clear'] = [];
	params['clear'][0] = 'input, select';

	params['remove'] = [];
	params['remove'][0] = '.error_message, .select2';

	addRow($('.table_data'), $('.table_data tr:eq(1)'), params);
}

function delRows()
{
	if($('.deleteRow:checked').length > 0 && confirm('Вы хотите удалить выбранные строки?'))
	{
		if($('.table_data .row_number').length > 1)
		{
			let addEmptyTr = null;
			if($('.deleteRow:checked').length == $('.deleteRow').length)
			{
				addEmptyTr = $('.deleteRow').parents('tr').get(0);
			}

			$('.deleteRow:checked').parents('tr').remove();
			if(addEmptyTr)
			{
				$('.table_data_block table tbody').append(addEmptyTr);
				addNewRow();
			}
		}

		$('.table_data input.row_number').each(function(index){
			$(this).val(index);
		});
	}
	$('#delAll, .deleteRow').prop('checked', false);
	$(window).trigger('resize');
}

function checkValues()
{
	var mess = '';
	var err = false;
	if($('#shop').val() == 0)
	{
		$('#select2-shop-container').parents('.select2-selection').addClass('error_input');
		mess += 'Не указан магазин<br>';
		err = true;
	}

	let kodExist = false;
	$('.kodNomenkatur').each(function(){

		if($(this).val() != '')
		{
			kodExist = true;
			return false;
		}
	});

	if(!kodExist)
	{
		mess += 'Не указано ни одного товара<br>';
		err = true;
	}

	if(err)
	{
		showMessage('error', false, mess);
		return false;
	}
}