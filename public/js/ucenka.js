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
			success:function(data){
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
				width:500,
				shrinkToFit:true,
				colModel:[
				   		{label:'Номер',   				name:'num',		width:50,align:"center"},
				   		{label:'Магазин', 				name:'name',	width:180},
				   		{label:'Одобрено',				name:'status',	width:150,align:"center"},
				   		{label:'Наименование товара', 	name:'tov_name',width:250},
				   		{label:'Дата подачи заявки',	name:'addDate',	width:120,align:"center"},
					],
				multiselect:false,
				pager:'#jqGridpager',
				ondblClickRow: function(rowid)
				{
					if(!rowid)
						return;
					window.location.href="/ucenka/edit/"+rowid;
				},
				// onPaging: function()
				// {
				// 	setFrozenHeightTd();
				// }
			});

			grid.jqGrid('navGrid','#jqGridpager', {edit:false,add:false,del:false,search:false,refresh:false});
			// grid.jqGrid('setFrozenColumns');
			// setFrozenHeightTd();
		}
		else if($("#jqGridEdit").length > 0)
		{
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			let colModelEditOp;
			if(isKM)
			{
				colModelEditOp = [
			   		{label:'ID',name:'ID',hidden:true},
			   		{label:'Номер',name:'num',width:30,align:"center"},
			   		{label:'Магазин',name:'shop',width:100},
			   		{label:'Код Номенклатуры',name:'kod',width:70},
			   		{label:'Наименование',name:'name',width:150},
			   		{label:'Срок годности',name:'srok',width:50,align:"center"},
			   		{label:'Причина уценки',name:'reason',width:50,align:"center"},
			   		{label:'Остаток',name:'ostatok',width:50,align:"center"},
				];
			}
			else
			{
				colModelEditOp = [
			   		{label:'ID',				name:'ID',hidden:true},
			   		{label:'Номер',				name:'num',width:30,align:"center"},
			   		{label:'Магазин',			name:'shop',width:100},
				 	{label:'Код Номенклатуры',	name:'kod',width:70,editable:true,edittype:'text'},
			   		{label:'Наименование',		name:'name',width:150,editable:true},
			   		{label:'Срок годности',		name:'srok',width:50,align:"center",editable:true,edittype:'text'},
			   		{label:'Причина',			name:'reason',width:50,align:"center",editable:true,edittype:'select',editoptions:{value:reasonVariants}},//{dataUrl:'include/test.php'}
			   		{label:'Остаток',			name:'ostatok',width:50,align:"center",editable:true,edittype:'text'},
				];
			}

			if(isKM)
			{
				colModelEditOp.push(
					{label:'Скидка %',name:'skidka',width:50,align:"center",editable:true,edittype:"text"},
					{
						label:'Одобрить',
						name:'approve',
						index:'approve',
						width:70,
						align:"center",
						editable:true,
						formatter:'select',
						edittype:"select",
						editoptions:{value:approveVariants},
					},
					{label:'Комметарий',name:'refusal_comment',index:'refusal_comment',width:100,align:"center",editable:true,edittype:'text'}
					);
			}

			grid = $("#jqGridEdit");
			grid.jqGrid({
				url: 	'/ucenka/ajaxJsonEdit/'+grid.data('id'),
				// editurl:'/ucenka/ajaxJsonEditSubmit',
				editurl:'clientArray',
				datatype: "json",
				height:300,
				width:500,
				colModel:colModelEditOp,
				multiselect:true,
				pager:'#jqGridEditPager',
				ondblClickRow:function(rowid)
				{
					grid.editRow(rowid);
					events();
				}
			});

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
					url:'/ucenka/ajaxJsonEditSubmit',
					type:'post',
					dataType:'json',
					data:'d='+json+'&appId='+grid.data('id'),
					success:function(d){

						if(d.success)
						{
							showMessage('success', false, 'Заявка успешно сохранена.');
							setTimeout(function(){
								window.location.href = '/ucenka/list';
							}, 1000);
						}
						else if(d.errors)
						{
							let str = '';
							for (i in d.errors) {

								if(i == 0)
								{
									str += d.errors[i]+'<br>';
								}
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
				 	{label:'Код Номенклатуры',		name:'kod',width:70,editable:true,edittype:'text'},
			   		{label:'Наименование',			name:'name',width:150,editable:true},
			   		{label:'Срок годности',			name:'srok',width:50,align:"center",editable:true,edittype:'text'},
			   		{label:'Причина',				name:'reason',width:50,align:"center",editable:true,edittype:'select',editoptions:{value:reasonVariants}},//{dataUrl:'include/test.php'}
			   		{label:'Остаток',				name:'ostatok',width:50,align:"center",editable:true,edittype:'text'},
				],
				ondblClickRow:function(rowid)
				{
					grid.editRow(rowid);
					events();
				}
			});
			addJqGridRow();
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

function addJqGridRow()
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
}
function delJqGridRows()
{
	let ids = grid.jqGrid('getGridParam','selarrrow');
	for (var i = ids.length - 1; i >= 0; i--){
		grid.jqGrid('delRowData', ids[i]);
	}
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