<form id='frm-post-script' method='post'>
<input type='hidden' class='required' name='id' value='<?=$script['id']?>' >
<input type='submit' value= ' save script '>
<table width=100%>

    <tr>
        <td>Active</td>
        <td>
            <!--GG tamad MODE!! hardcoded nalang :)-->
            <select name='is_active'>
                <option value=1 <?=(($script['is_active']=0)? 'selected' : '')?>>Active</option>
                <option value=0 <?=(($script['is_active']=0)? 'selected' : '')?>>Inactive</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width=5%>Order</td>
        <td><input class='required' name='order' value='<?=$script['order']?>' size=3 maxlength=3></td>
    </tr>
    <tr>
        <td>Subject</td>
        <td><input class='required' name='script_label' value='<?=$script['script_label']?>' size=100></td>
    </tr>
    <tr>
        <td colspan=2>
            <textarea class='required' name='script' rows=20><?=$script['script']?></textarea>
        </td>
    </tr>
</table>
</form>

<script>
	$(function(){
        tinyMCE.init({
            mode : "textareas",
            theme: "modern",
            plugins: [
                "advlist autolink link image lists charmap print preview hr  pagebreak",
                "searchreplace wordcount visualblocks  code fullscreen   nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor"
            ],
            content_css: "css/content.css",
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview  fullpage | forecolor backcolor | fontselect |  fontsizeselect",
            theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
            font_size_style_values: "12px,13px,14px,16px,18px,20px",
            style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
            ]
        });


        $('#frm-post-script').submit(function(){
            if(!$(this).valid()){
                return false;
            }
        })
	})
</script>