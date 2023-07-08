<div id='div-locked'></div>

<?if(ENABLE_AVAILABLE_LEADS || $user_type == ADMIN_CODE ){?>
<fieldset>
    <legend>Available Leads limited to <?=LIMIT?> records</legend>

    <div id='selector'></div>

    <table class='tbl-lead-views' id='tbl-lead-views'>
        <?
        //No callresult to be shonwn!
		//CB should show the result (6/11/2016)
        $noCr = array('BS','NA');
        if(count($contacts) <= 0){
            ?>
            <tr>
                <td colspan=10>
                    <center><span class='warning'>no record found</span>
                </td>
            </tr>
        <?
        }else{
            ?>
            <tr>
                <td> </td>
                <td>fullname</td>
                <td>call date</td>
                <td>call result</td>
                <td>supple fullname</td>
            </tr>
        <?
        }
        $i=1;
        $ctr=0;

        foreach($contacts as $detail){

            if($ctr == ITEM_PER_PAGE){
                $i++;
                $ctr=0;
            }
            ?>
            <tr class='tr-list selector_selection selector_page-<?=$i?>' >
				<!-- PRINCIPAL NAME -->
                <td>
                    <?if(isset($privs[189])){?>
                        <input type='checkbox' <?=($detail['is_active']) ? 'checked' : ''?> recordID='<?=$detail['id']?>' class='chk-contact-active'>
                    <?}?>
                </td>
				
				<!-- CALLDATE -->
                <td>
				<?php
                    $fullname = $detail['firstname']. ' ' . $detail['lastname'];
                    if(trim($fullname) == ''){
                        echo $detail['pd_name'];
                    }else{
                        echo $fullname;
                    }

				?>
				</td>
                <td><?=((!in_array($detail['callresult'],$noCr)) ? $detail['calldate'] : '')?></td>
				
				<!-- CALLRESULT -->
                <td>
                    <?=((isset($cr[$detail['callresult']]) && !in_array($detail['callresult'],$noCr)) ? $cr[$detail['callresult']] : '')?>

                    <?if(in_array($detail['callresult'],$withSubCR)  && !in_array($detail['callresult'],$noCr)){?>
                        <?=isset($subCr[$detail['sub_callresult']]) ?  "(<i>{$subCr[$detail['sub_callresult']]} </i>)" : ''?>
                    <?}elseif($detail['callresult']=='AG'){?>
                        (<?=isset($ag_type[$detail['ag_type']]) ? $ag_type[$detail['ag_type']] : ''?>)
                    <?}?>
                </td>
				
				<!-- SUPPLE NAME -->
				<td>
					<?=$detail['supple_fullname'];?>
				</td>

                <?if((isset($privs[174]) && !isset($restricted[$detail['callresult']]) ) || isset($privs[186])){?>
                    <td class='td-pick'><a href='<?=base_url()?>main/edit/<?=$detail['id']?>' class='a-link '>pick</a></td>
                <?}?>

                <td>
                    <?if(isset($privs[187])){?>
                        <a href='<?=base_url()?>main/edit/<?=$detail['id']?>/1' class='a-link-view '>view</a>
                    <?}?>

                </td>



                <td >
                    <?if(isset($privs[185])){?>
                        <a href='<?=base_url()?>main/pop/<?=$detail['id']?>' class='a-link-pop '>
                            <?=(($detail['forcedpop'] == 1) ? ' cancel pop-out ' : ' pop-out ')?>
                        </a>
                    <?}?>
                </td>


            </tr>
            <?
            $ctr++;
        }?>
    </table>

</fieldset>
<?}?>

<div id='div-popout'></div>

<div id='div-callback'></div>
<script>

    function renderPage(pageNumber,selector)
    { 
        var page="."+selector+"_page-"+pageNumber;
				console.log('page:'+page);
				console.log('selection:'+'.'+selector+'_selection');				
        $('.'+selector+'_selection').hide()
				
        $(page).show()

    }

    function a_link_fx(selector,itemCounts){

        var itemPerAge = Math.round('<?=ITEM_PER_PAGE?>');

        if(itemCounts > itemPerAge)
        {

            renderPage(1,selector);
            $('#'+selector).pagination({
                items: itemCounts,
                itemsOnPage: <?=ITEM_PER_PAGE?>,
                cssStyle: 'compact-theme',
                onPageClick: function(pageNumber){renderPage(pageNumber,selector)}
            });

        }


        //$(".tr-list").mouseover(function(){$(this).find('a.a-link').removeClass('hidden');})
        //$(".tr-list").mouseout(function(){$(this).find('a.a-link').addClass('hidden');})

        $(".tr-list").mouseover(function(){$(this).addClass('tr_highlight');})
        $(".tr-list").mouseout(function(){$(this).removeClass('tr_highlight');})

        $(".a-link").inlineConfirmation({
            confirmCallback: function() {

                var url =  $(this).parent().parent().parent().find('a.a-link').prop('href');
                $.ajax({
                    url:url,
                    success:function(data){
                        $("#div-contact-list").html(data);
                    }
                })

            },
            expiresIn: 3,
            confirm:"<a href='#' >Yes</a>",
            separator:" | ",
            cancel:"<a href='#'>No</a>"

        });

        $(".a-link-view").click(function(){

            var url = $(this).prop('href');
            $.ajax({
                url:url,
                success:function(data){
                    $("#div-contact-list").html(data);
                }
            })

            return false;
        })

    }

    $(document).ready(function(){
        a_link_fx('selector','<?=count($contacts)?>');
        //get locked records
        $("#div-locked").load('<?=base_url()?>main/locked');

        //get popout records
        $("#div-popout").load('<?=base_url()?>main/popout');


        //get callback records
        $("#div-callback").load('<?=base_url()?>main/callback');

        $(".a-link-pop").click(function(){
            var url = $(this).prop('href');
            do_modal(url,'div-general-modal','',150,300);

            return false;
        })

        $('.chk-contact-active').change(function(){
            var is_active= 0;
            if($(this).prop('checked')==  true)
                is_active= 1;

            var recordID = $(this).attr('recordID');

            $.ajax({
                url:'<?=base_url()?>main/single_activator/'+recordID+'/'+is_active,
                success:function(data){
                    //alert(data);
                }
            })
        })

    })
</script>