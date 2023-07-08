<form id='frm-post-script' method='post'>
<input type='submit' value= ' save faqs '>
<textarea class='required' name='faqs' rows=20><?=$script?></textarea>
</form>

<script>
	$(function(){
		tinyMCE.init({mode : "textareas",theme : "modern"});
	})
</script>