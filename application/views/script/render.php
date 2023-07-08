<?if(!$modal){?>
    <a class='link-full-size'>[full size]</a>
<?}?>
<div class='div-block'>
    <table width=100%>
        <tr>
            <td style='text-align:left'>
                <?if(!empty($prevpage)){?>
                    <input type='button' value='<?=$prevpage['script_label']?>' class='button btn-render-script<?=(($modal==1) ? '-modal' : '' )?>'  page='<?=$prevpage['id']?>'>
                <?}?>
            </td>
            <td style='text-align:right'>
                <?if(!empty($nextpage)){?>
                    <input type='button' value='<?=$nextpage['script_label']?>' class='button btn-render-script<?=(($modal==1) ? '-modal' : '' )?>' page='<?=$nextpage['id']?>'>
                <?}?>
            </td>
        </tr>
    </table>
</div>

<div class='div-block'>
<?=$script['script']?>
</div>

<div class='div-block'>
    <table width=100%>
        <tr>
            <td style='text-align:left'>
                <?if(!empty($prevpage)){?>
                    <input type='button' value='<?=$prevpage['script_label']?>' class='button btn-render-script<?=(($modal==1) ? '-modal' : '' )?>'  page='<?=$prevpage['id']?>'>
                <?}?>
            </td>
            <td style='text-align:right'>
                <?if(!empty($nextpage)){?>
                    <input type='button' value='<?=$nextpage['script_label']?>' class='button btn-render-script<?=(($modal==1) ? '-modal' : '' )?>' page='<?=$nextpage['id']?>'>
                <?}?>
            </td>
        </tr>
    </table>
</div>

<div id='main-modal-panel' class='hidden'></div>

<script>
    $(function(){

        $('.btn-render-script').click(function(){
            var page = $(this).attr('page');
            var url = '<?=base_url()?>script/render/'+page;


            do_ajax(url,'POST','','div-script-holder');
        })

        $('.btn-render-script-modal').click(function(){
            var page = $(this).attr('page');
            var url = '<?=base_url()?>script/render/'+page+'/1';
            var return_url = '<?=base_url()?>script/render/'+page+'/0';
            global_return_url = return_url;
            ajax_modal(url,500,1000);
        })

        $('.link-full-size').click(function(){
            var curpage = '<?=$script['id']?>';
            var url = '<?=base_url()?>script/render/'+curpage+'/1';
            ajax_modal(url,500,1000,'','div-script-holder');
        })

    })
</script>