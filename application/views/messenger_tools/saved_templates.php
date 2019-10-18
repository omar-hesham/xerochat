<?php 
	$this->load->view("include/upload_js");

	$image_upload_limit = 1; 
	if($this->config->item('messengerbot_image_upload_limit') != '')
	$image_upload_limit = $this->config->item('messengerbot_image_upload_limit'); 
?>

<!-- new datatable section -->

<section class="section section_custom">
  <div class="section-header">
    <h1><i class="fa fa-th-large"></i> <?php echo $this->lang->line('Saved Templates'); ?> </h1>
    
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="<?php echo base_url('messenger_bot/bot_menu_section'); ?>"><?php echo $this->lang->line("Messenger Bot Features"); ?></a></div>
      <div class="breadcrumb-item"><?php echo $page_title; ?></div>
    </div>
  </div>

  <?php $this->load->view('admin/theme/message'); ?>

  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body data-card">

            <div class="input-group mb-3" id="searchbox">

        		<?php if($this->session->userdata('user_type')=='Member') 
        		{?>
        			<div class="input-group-prepend">
        				<select name="search_template_access" id="search_template_access"  class="form-control select2">
        					<option value=""><?php echo $this->lang->line("All Templates") ?></option>
        					<option value="private"><?php echo $this->lang->line("My Templates") ?></option>
        					<option value="public"><?php echo $this->lang->line("Admin Templates") ?></option>							
        				</select>
        			</div>
        			<?php 	
        		} 
        		else 
        		{?>
        			<div class="input-group-prepend">
        				<select name="search_template_access" id="search_template_access"  class="form-control select2">
        					<option value=""><?php echo $this->lang->line("All Templates") ?></option>
        					<option value="private"><?php echo $this->lang->line("Private") ?></option>
        					<option value="public"><?php echo $this->lang->line("Public") ?></option>							
        				</select>
        			</div>
        		<?php 	
        	    }?>
        		
        		<input id="search_template_name" name="search_template_name" value="" class="form-control" autofocus placeholder="<?php echo $this->lang->line("Template Name") ?>" aria-describedby="basic-addon2" style="max-width: 30%" >
        		
                <div class="input-group-append">
                      <button class="btn btn-primary" id="search_submit" type="button"><i class="fas fa-search"></i> <span class="d-none d-sm-inline"><?php echo $this->lang->line('Search'); ?></span></button>
                </div>
            </div>
            
            <div class="table-responsive2">
              <table class="table table-bordered" id="mytable">
                <thead>
                  <tr>
                    <th>#</th>      
                    <th style="vertical-align:middle;width:20px">
                        <input class="regular-checkbox" id="datatableSelectAllRows" type="checkbox"/><label for="datatableSelectAllRows"></label>        
                    </th>
                    <th><?php echo $this->lang->line("Template Name")?></th>
                    <th><?php echo $this->lang->line("Template Access")?></th>
                    <th><?php echo $this->lang->line("Saved At")?></th>
                    <th><?php echo $this->lang->line("Actions")?></th>
                  </tr>
                </thead>
              </table>
            </div>            
          </div>

        </div>
      </div>
    </div>
    
  </div>
</section>



<?php
	$somethingwentwrong = $this->lang->line("Something went wrong.");
	$doyoureallywanttodeletethiscampaign = $this->lang->line("Do you really want to delete this template?");
	$success_msg = $this->lang->line("Bot template has been saved to database successfully.")
?>

<script type="text/javascript">
  $("document").ready(function(){
  	var base_url = "<?php echo base_url(); ?>";
  	var success_msg = "<?php echo $success_msg; ?>";
    $('[data-toggle="popover"]').popover(); 
    $('[data-toggle="popover"]').on('click', function(e) {e.preventDefault(); return true;});  
    
    $('#export_bot_modal').on('hidden.bs.modal', function () { 
    	table.draw();
    });   

    $(document).on('click','.load_preview_modal',function(e){
      e.preventDefault();
      var item_type = $(this).attr('item_type');
      var file_path = $(this).next().val();
      var user_id = "<?php echo $this->user_id; ?>";

      var res = file_path.match(/http/g);
      if(file_path != '' && res === null)
	      file_path = base_url+"upload/image/"+user_id+"/"+file_path;

      $("#preview_text_field").val(file_path);
      if(item_type == 'image')
      {
        $("#modal_preview_image").attr('src',file_path);
        $("#image_preview_div_modal").show();
        $("#video_preview_div_modal").hide();
        $("#audio_preview_div_modal").hide();
        
      }
      $("#modal_for_preview").modal();
    });

    var perscroll;
    var table = $("#mytable").DataTable({
        serverSide: true,
        processing:true,
        bFilter: false,
        order: [[ 2, "desc" ]],
        pageLength: 10,
        ajax: {
            url: base_url+'messenger_bot/saved_templates_data',
            type: 'POST',
            data: function ( d )
            {
                d.search_template_access = $('#search_template_access').val();
                d.search_template_name = $('#search_template_name').val();
            }
        },          
        language: 
        {
          url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
        },
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        columnDefs: [
          {
              targets: [0,1],
              visible: false
          },
          {
              targets: '',
              className: 'text-center'
          },
          {
              targets: [0,1,3,5],
              sortable: false
          }
        ],
        fnInitComplete:function(){  // when initialization is completed then apply scroll plugin
            if(areWeUsingScroll)
            {
              if (perscroll) perscroll.destroy();
              perscroll = new PerfectScrollbar('#mytable_wrapper .dataTables_scrollBody');
            }
        },
        scrollX: 'auto',
        fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
            if(areWeUsingScroll)
            {
              if (perscroll) perscroll.destroy();
              perscroll = new PerfectScrollbar('#mytable_wrapper .dataTables_scrollBody');
            }
        }
    });


    $(document).on('click', '#search_submit', function(event) {
      event.preventDefault(); 
      table.draw();
    });

    $('.modal').on("hidden.bs.modal", function (e) { 
        if ($('.modal:visible').length) { 
            $('body').addClass('modal-open');
        }
    });


    $(document).on('click','.delete',function(e){
    	e.preventDefault();
    	swal({
    		title: '<?php echo $this->lang->line("Warning!"); ?>',
    		text: '<?php echo $this->lang->line("Do you really want to delete this template?"); ?>',
    		icon: 'warning',
    		buttons: true,
    		dangerMode: true,
    	})
    	.then((willDelete) => {
    		if (willDelete) 
    		{
    			var base_url = '<?php echo site_url();?>';
    			$(this).addClass('btn-progress');
    			$(this).removeClass('btn-circle');

    			var id = $(this).attr('id');

    			$.ajax({
    				context: this,
    				type:'POST' ,
    				url:"<?php echo site_url();?>messenger_bot/delete_template",
    				dataType: 'json',
    				data: {id:id},
    				context: this,
    				success:function(response){ 
    					$(this).removeClass('btn-progress');
    					$(this).addClass('btn-circle');
    					if(response == '1')
    					{
    						iziToast.success({title: '',message: '<?php echo $this->lang->line("Template successfully deleted."); ?>',position: 'bottomRight'});
    						table.draw();
    					}
    					else
    					{
    						iziToast.error({title: '',message: '<?php echo $this->lang->line("Something went wrong."); ?>',position: 'bottomRight'});
    					}
    				}
    			});
    		} 
    	});


    });


    $(document).on('click','.export_bot',function(e){
      e.preventDefault();
      var table_id = $(this).attr('table_id');
      $("#export_bot_modal").modal();

      $.ajax({
        type:'POST' ,
        url:"<?php echo site_url();?>messenger_bot/get_export_bot_form",
        data:{table_id:table_id},
        success:function(response){ 
           $('#export_bot_modal_body').html(response);  
        }
      });
    });

    $(document).on('change','input[name=template_access]',function(){
      var template_access = $(this).val();
      if(template_access=='private') $("#allowed_package_ids_con").addClass('hidden');
      else $("#allowed_package_ids_con").removeClass('hidden');
    });

    $(document).on('click','#export_bot_submit',function(e){
      e.preventDefault();
      var template_name = $("#template_name").val();
      var template_access = $('input[name=template_access]:checked').val();
      var allowed_package_ids = $("#allowed_package_ids").val();

      if(template_name=="")
      {
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please provide template name.');?>", 'warning');
        return;
      }

      if(template_access=="public" && allowed_package_ids==null)
      {
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('You must choose user packages to give them template access.');?>", 'warning');
        return;
      }

      $(this).addClass('btn-progress');
      var queryString = new FormData($("#export_bot_form")[0]);
      $.ajax({
            type:'POST' ,
            url: base_url+"messenger_bot/edit_export_bot",
            // dataType: 'JSON',
            data: queryString,
            cache: false,
            contentType: false,
            processData: false,
            context: this,
            success:function(response)
            { 
            	$(this).removeClass('btn-progress');
            	iziToast.success({title: '',message: success_msg,position: 'bottomRight'});
            }
        });

    });

    $(document).on('click', '#cancel_bot_submit', function(e){
    	e.preventDefault();
    	$("#export_bot_modal").modal('hide');
    	table.draw();
    });



  });
</script>





<div class="modal fade" id="export_bot_modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="padding-left: 30px;">
                <h5 class="modal-title"><i class="fa fa-edit"></i> <?php echo $this->lang->line("Edit Saved Template");?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="export_bot_modal_body">
            	<br><div class="text-center waiting previewLoader"><i class="fas fa-spinner fa-spin blue text-center" style="font-size: 40px;"></i></div></br>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal_for_preview" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-eye"></i> <?php echo $this->lang->line('item preview'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
      <div class="modal-body">
        <div id="image_preview_div_modal" style="display: none;">
          <img id="modal_preview_image" width="100%" src="">
        </div>
        <div id="video_preview_div_modal" style="display: none;">
          <video width="100%" id="modal_preview_video" controls>
            
          </video>
        </div>
        <div id="audio_preview_div_modal" style="display: none;">
          <audio width="100%" id="modal_preview_audio" controls>
            
          </audio>
        </div>
        <div>
          <input class="form-control" type="text" id="preview_text_field">
        </div>
      </div>
    </div>
  </div>
</div>
