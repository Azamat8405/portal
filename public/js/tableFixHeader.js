var $header = null;
$(function(){

	setTimeout(function(){

		$parHeader = $('#parentTableHeader');
		$parHeader.css({
		    'width':'82%',
		    'display':'block',
		    'position':'fixed',
		    'overflow':'hidden',
			'z-index':'95',
		});

		$header = $('#tableHeader');
		$header.css({
		    'background':'#fff',
			'width':'1000%',
			'position': 'relative',
		});

		var $thead = $('#tableTovs thead');
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
		$header.css({
				left: ($('#tableTovs').offset().left - $('#offset').offset().left)
			});
	}, 100);

	var $viewport = $(window);
	$viewport.scroll(function(){

		if(!$header)
			return;

		resizeTable();
	});

	resizeTable();
	$(window).resize(function(){

		resizeTable();
	});
});

function resizeTable()
{
	if($header)
	{
		$header.css({
			left: ($('#tableTovs').offset().left - $('#offset').offset().left)
		});
	}

	$table = $('.table_data_block');
	$table.css({
		'height': ($(window).height() - $table.offset().top - 10)
	});
	$('#parentTableHeader').width($table.width());
}

// setTimeout(function(){
// 	var css = [];
// 	var $tbody = $('#tableTovs tbody');
// 	$tbody.find('td').each(function(index){

// 		if(index > 2)
// 		{
// 			return;
// 		}
// 		css[index] = {
// 			'position':'fixed',
// 			'height':$(this).css('height'),
// 			'width':$(this).css('width'),
// 			'z-index': 500,
// 			'background': '#fff',
// 			'left':$(this).offset().left
// 		};
// 		// $(this).css();
// 	});
// 	$tbody.find('td').each(function(index){

// 		// $('#tableTovs thead th:eq('+index+')').css('min-width', css[index].width);
// 	console.log(css[index]);

// 		$(this).css(
// 			css[index]
// 		);
// 	});
// }, 300);

// : ;
// : 48px;
// : 41px;
// top: 226px;
// z-index: 500;
// background: #fff;