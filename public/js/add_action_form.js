$(function(){

	if($.fn.datepicker)
	{
		$('#start_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		});
		$('#end_date').datepicker({
			minDate: new Date(),
			dateFormat: "dd-mm-yy"
		});
	}
});