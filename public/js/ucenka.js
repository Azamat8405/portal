var grid;
var approveVariants;
approveVariants = '1:Одобрено;2:Отклонено';

var cache_kodNomenkatur = {};
var kodNomenkaturAvtocomplete = {
	source: function( request, response ){

		var el = this.element;


		var n_row = getRowNumber(el);
		var term = request.term;

		if(term in cache_kodNomenkatur)
		{
			$(el).removeClass('error_input');
			$('#'+n_row+'_name').val(cache_kodNomenkatur[term][0].label);
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
				$('#'+n_row+'_name').val(data[0].label);
			},
			error:function(){
				$(el).addClass('error_input');
				$('#'+n_row+'_name').val('');
				showMessage('error', false, 'Не найден товар с указанным кодом в разделе "Детское питание".');
			}
		});
	},
	minLength:2,
}

$(function () {

	$('.form').keydown(function(e)
	{
		if(e.keyCode == 13)
		{
			e.preventDefault();
			return false;
        }
	});

	if($.fn.jqGrid)
	{
		if($("#jqGridList").length > 0)
		{
			grid = $("#jqGridList");
			grid.jqGrid({
				url:'/ucenka/ajaxJsonList',
				datatype: "json",
				height:300,
				colNames:['Номер','Магазин','Одобрена','Наименование товара'],
				colModel:[
				   		{name:'num',width:30,align:"center"},
				   		{name:'name',width:100},
				   		{name:'status',width:50,align:"center"},
				   		{name:'tov_name',width:100,},
				   	],
				multiselect:false,
				pager: '#jqGridpager',
				ondblClickRow: function(rowid)
				{
					if(!rowid)
						return;
					window.location.href="/ucenka/edit/"+rowid;
				}
			});
			grid.jqGrid('navGrid','#jqGridpager', {edit:false,add:false,del:false,search:false,refresh:false});
		}
		else if($("#jqGridEdit").length > 0)
		{
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			grid = $("#jqGridEdit");
			grid.jqGrid({
				url: 	'/ucenka/ajaxJsonEdit/'+grid.data('id'),
				editurl:'/ucenka/ajaxJsonEditSubmit',
				datatype: "json",
				height:300,
				colNames:['Номер','Магазин','Код Номенклатуры','Наименование','Срок годности','Причина уценки','Остаток','Скидка','Одобрить','Комметарий'],
				colModel:[
				   		{name:'num',width:30,align:"center"},
				   		{name:'shop',width:100},
				   		{name:'kod',width:70},
				   		{name:'name',width:150},
				   		{name:'srok',width:50,align:"center"},
				   		{name:'reason',width:50,align:"center"},
				   		{name:'ostatok',width:50,align:"center"},
				   		{name:'skidka',width:50,align:"center"},
				   		{name:'approve',index:'approve',width:70,align:"center",editable:true,edittype:"select",editoptions:{value:approveVariants}},
				   		{name:'refusal_comment',index:'refusal_comment',width:100,align:"center",editable:true,edittype:'text'},
					],
				multiselect:false,
				pager:'#jqGridEditPager',
				ondblClickRow:function(rowid)
				{
					grid.editRow(rowid);
				}
			});

			$("#save").click( function(e) {
				e.preventDefault();

				grid.find('tr[editable=1]').each(function(){

					grid.jqGrid('saveRow', this.id, function(d, l, o){

						if(d.responseText == 1)
						{
							return true;
						}
						return false;
					}, '', {'app_id': grid.data('id')});
				});
			});

			grid.jqGrid('navGrid','#jqGridEditPager', {edit:false,add:false,del:false,search:false,refresh:false});
		}
		else if($("#jqGridAdd").length > 0)
		{
			$.ajaxSetup({
				headers:{
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			grid = $("#jqGridAdd");
			grid.jqGrid({
				height:300,
				multiselect:true,
				colModel:[
				   		{label:'Код Номенклатуры',name:'kod',width:70,editable:true,edittype:'text'},
				   		{label:'Наименование',	name:'name',width:150,editable:true},
				   		{label:'Срок годности',	name:'srok',width:50,align:"center",editable:true,edittype:'text'},
				   		{label:'Причина',		name:'reason',width:50,align:"center",editable:true,edittype:'select', editoptions:{value:reasonVariants}},//{dataUrl:'include/test.php'}
				   		{label:'Остаток',		name:'ostatok',width:50,align:"center",editable:true,edittype:'text'},
					],
				ondblClickRow:function(rowid)
				{
					grid.editRow(rowid);
					events();
				}
			});
			addRow();
			$("#save").click( function(e) {
				e.preventDefault();

				if($('input.error_input:visible').length)
				{
					return showMessage('error', false, 'Необходимо поправить ошибки формы.');
				}

				grid.find('tr[editable=1]').each(function(){
					save(grid, this.id);
				});

				let json = JSON.stringify(grid.getRowData());
				$.ajax({
					url:'/ucenka/ajaxAddSubmit',
					type:'post',
					dataType:'json',
					data:'d='+json,
					success:function(d){

						if(d.success)
						{
							showMessage('success', false, 'Заявка успешно добавлена.');
							setTimeout(function(){
								window.location.href = '/ucenka/list';
							}, 1000);
						}
						else if(d.errors)
						{
							let str = '';
							for (i in d.errors) {

								if(i == 0)
									str += d.errors[i]+'<br>';
								else
								{
									grid.editRow(i);
									events();

									for (ind in d.errors[i]) {

										$('input#'+i+'_'+ind).before( '<div class="error_message">'+d.errors[i][ind]+'</div>' );
									}
								}
							}
							if(str != '')
								showMessage('error', false, str);
						}
					}
				});
			});
		}
	}
});

function addRow()
{
	var r = grid.jqGrid('getGridParam','records');
	var parameters = 
	{
		rowID:++r,
	    initdata: {},
	    position :"last",
	    useDefValues : true,
	    useFormatter : false,
	    addRowParams : {extraparam:{}}
	}
	grid.jqGrid('addRow',parameters);
	events();


	// $('input').button();
	// var select2Common = {
	// 	width:'95%',
	// 	minimumResultsForSearch:Infinity,
	// };
	// $('select').select2(select2Common);

}
function save(grid, id)
{
	grid.saveRow(id, false, 'clientArray');
}
function getRowNumber(el)
{
	return $(el).parents('tr').attr('id');
}
function events()
{
	$('input[id$=_kod]').autocomplete(kodNomenkaturAvtocomplete);

	//
	let d = new Date();
	let min = new Date();
	min.setTime(d.getTime() + 86400000);
	let max = new Date();
	max.setTime(d.getTime() + 6048000000);
	$('input[id$=_srok]').datepicker({
		minDate:min,
		maxDate:max,
	});

	$('input[type=text]').on('change', function(){
		$(this).parents('td').find('.error_message').remove();
	});
}

function delRows()
{
	let ids = grid.jqGrid('getGridParam','selarrrow');
	for (var i = ids.length - 1; i >= 0; i--) {

		grid.jqGrid('delRowData', ids[i]);
	}
}