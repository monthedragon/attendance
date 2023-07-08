<form id='frm-post-script' method='post'>
<input type='submit' value= ' save script '>
<textarea class='required' name='script' rows=20><?=$script?></textarea>
</form>

<script>
	$(function(){
		tinyMCE.init({mode : "textareas",theme : "modern"});
	})
</script>