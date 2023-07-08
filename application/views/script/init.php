<div id='div-script-holder' style='min-height:200px;max-height:200px;overflow:scroll;'>

</div>

<script>
    $(function(){
        var url = '<?=base_url()?>script/render/';
        do_ajax(url,'POST','','div-script-holder');
    })
</script>