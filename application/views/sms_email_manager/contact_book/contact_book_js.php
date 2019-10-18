<script>
	$(document).ready(function($) 
	{
		var base_url = '<?php echo base_url(); ?>';

		$('[data-toggle=\"tooltip\"]').tooltip();

		$(document).on('change', '#rows_number', function(event) {
		    event.preventDefault();
		    $("#group_search_submit").click();
		});

		$(document).on('keypress', '.group_search', function(event) {
		    if(event.which == 13) event.preventDefault();
		}); 

		$(document).on('click', '.add_group', function(event) {
		    event.preventDefault();
		    $("#add_contact_group_modal").modal();
		});

		$(document).on('click', '#save_group', function(event) {
		    event.preventDefault();
		    
		    var group_name = $("#group_name").val();
		    if(group_name == '')
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Group Name is Required"); ?>', 'warning');
		        return;
		    }

		    $(this).addClass('btn-progress')
		    var that = $(this);

		    $.ajax({
		        url: base_url+'sms_email_manager/add_contact_group_action',
		        type: 'POST',
		        data: {group_name: group_name},
		        success:function(response)
		        {
		            $(that).removeClass('btn-progress');

		            if(response == "1")
		            {
		                iziToast.success({title: '',message: '<?php echo $this->lang->line('Contact Group has been created successfully.'); ?>',position: 'bottomRight'});
		            } else if(response == "2")
		            {
		            	iziToast.error({title: '',message: '<?php echo $this->lang->line('Group Name Already Exists, please try with different one.'); ?>',position: 'bottomRight'});
		            } 
		            else 
		            {
		                iziToast.error({title: '',message: '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>',position: 'bottomRight'});
		            }

		        }
		    })
		});

		$(document).on('click', '.edit_group', function(event) {
		    event.preventDefault();

		    $("#update_contact_group_modal").modal();
		    var group_id = $(this).attr("group_id");
		    var loading = '<div class="text-center waiting"><i class="fas fa-spinner fa-spin blue text-center" style="font-size:40px"></i></div>';

		    $("#group_body").html(loading);

		    $.ajax({
		        url: base_url+'sms_email_manager/ajax_get_group_info',
		        type: 'POST',
		        data: {group_id: group_id},
		        success:function(response)
		        {
		            if(response)
		            {
		                $("#group_body").html(response);
		            }
		        }     
		    })

		});

		$(document).on('click', '#update_group', function(event) {
		    event.preventDefault();

		    var table_id = $("#table_id").val();
		    var group_name = $("#update_group_name").val();
		    if(group_name == '')
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Group Name is Required"); ?>', 'warning');
		        return;
		    }

		    $(this).addClass('btn-progress')
		    var that = $(this);

		    $.ajax({
		        url: base_url+'sms_email_manager/ajax_update_group_info',
		        type: 'POST',
		        data: {table_id: table_id,group_name:group_name},
		        success:function(response)
		        {
		            $(that).removeClass('btn-progress');

	                if(response == "1")
	                {
	                    iziToast.success({title: '',message: '<?php echo $this->lang->line('Contact Group has been Updated successfully.'); ?>',position: 'bottomRight'});
	                } else if(response == "2")
	            	{
	            		iziToast.error({title: '',message: '<?php echo $this->lang->line('Group Name Already Exists, please try with different one.'); ?>',position: 'bottomRight'});
	            	}  
	                else 
	                {
	                    iziToast.error({title: '',message: '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>',position: 'bottomRight'});
	                }
		        }     
		    })
		});

		var Doyouwanttodeletethisrecordfromdatabase = "<?php echo $this->lang->line('Do you want to detete this record?'); ?>";
		$(document).on('click','.delete_group',function(e){
		    e.preventDefault();
		    swal({
		        title: '<?php echo $this->lang->line("Are you sure?"); ?>',
		        text: Doyouwanttodeletethisrecordfromdatabase,
		        icon: 'warning',
		        buttons: true,
		        dangerMode: true,
		    })
		    .then((willDelete) => {
		        if (willDelete) 
		        {
		            var table_id = $(this).attr('group_id');
		            var that = $(this);
		            $.ajax({
		                context: this,
		                type:'POST' ,
		                url:"<?php echo base_url('sms_email_manager/delete_contact_group')?>",
		                data:{table_id:table_id},
		                success:function(response)
		                { 
		                    if(response == '1')
		                    {
		                        iziToast.success({title: '',message: '<?php echo $this->lang->line('Contact Group has been deleted successfully.'); ?>',position: 'bottomRight',timeout: 3000});
		                        $(that).parent().parent().parent().parent().parent().remove();
		                    } else
		                    {
		                        iziToast.error({title: '',message: '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>',position: 'bottomRight',timeout: 3000});
		                    }

		                    // setTimeout(function(){ location.reload(); }, 3000);
		                }
		            });
		        } 
		    });
		});


		// this is for contact list table
		var perscroll1;
		var table1 = $("#mytable1").DataTable({
		    serverSide: true,
		    processing:true,
		    bFilter: false,
		    order: [[ 2, "desc" ]],
		    pageLength: 10,
		    ajax: 
		    {
		      "url": base_url+'sms_email_manager/contact_lists_data',
		      "type": 'POST',
		      data: function ( d )
		      {
		        d.group_id = $('#group_id').val();
		        d.contact_list_searching = $('#contact_list_searching').val();
		      }
		    },
		    language: 
		    {
		      url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
		    },
		    dom: '<"top"f>rt<"bottom"lip><"clear">',
		    columnDefs: [
		        {
		          targets: [2],
		          visible: false
		        },
		        {
		          targets: [0,1,2,3,4,5],
		          className: 'text-center'
		        },
		        {
		          targets: '',
		          sortable: false
		        }
		    ],
		    fnInitComplete:function(){  // when initialization is completed then apply scroll plugin
		        if(areWeUsingScroll)
		        {
		            if (perscroll1) perscroll1.destroy();
		            perscroll1 = new PerfectScrollbar('#mytable1_wrapper .dataTables_scrollBody');
		        }
		    },
		    scrollX: 'auto',
		    fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
		        if(areWeUsingScroll)
		        { 
		            if (perscroll1) perscroll1.destroy();
		            perscroll1 = new PerfectScrollbar('#mytable1_wrapper .dataTables_scrollBody');
		        }
		    }
		});

		$(document).on('change', '#group_id', function(event) {
		  event.preventDefault(); 
		  table1.draw();
		});

		$(document).on('click', '#contact_list_search_submit', function(event) {
		  event.preventDefault(); 
		  table1.draw();
		});
		// end of contact list table


		$(document).on('click', '.add_new_contact', function(event) {
		    event.preventDefault();
		    $("#add_contact_form_modal").modal();
		});


		function validateEmail(email) {
		  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/; 
		  return regex.test(email);
		}

		$(document).on('click', '#save_contact', function(event) {
		    event.preventDefault();

		    var first_name = $("#first_name").val();
		    var last_name = $("#last_name").val();
		    var contact_email = $("#contact_email").val();
		    var phone_number = $("#phone_number").val();
		    var contact_group_name = $("#contact_group_name").val();

		    if(first_name == "" && last_name == "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Name is Required"); ?>', 'warning');
		        return;
		    }

		    if(contact_email == "" && phone_number == "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Email/Phone number is Required"); ?>', 'warning');
		        return;
		    }

		    if(!validateEmail(contact_email) && contact_email != "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Please provide valid email address"); ?>', 'warning');
		        return;
		    }

		    if(contact_group_name == "" || contact_group_name == null || typeof(contact_group_name) == "undefined")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Contact Group is Required"); ?>', 'warning');
		        return;
		    }

		    $(this).addClass('btn-progress')
		    var that = $(this);


		    var alldatas = new FormData($("#contact_add_form")[0]);

		    $.ajax({
		        url: base_url+'sms_email_manager/ajax_create_new_contact',
		        type: 'POST',
		        dataType: 'JSON',
		        data: alldatas,
		        cache: false,
		        contentType: false,
		        processData: false,
		        success:function(response)
		        {
		            $(that).removeClass('btn-progress');

		            if(response.status == "1")
		            {
		                iziToast.success({title: '',message: response.msg,position: 'bottomRight'});

		            } else 
		            {
		                iziToast.error({title: '',message: response.msg,position: 'bottomRight'});
		            }

		        }
		    })

		});

		$(document).on('click', '.edit_contact', function(event) {
		    event.preventDefault();
		    $("#update_contact_form_modal").modal();

		    var table_id = $(this).attr("table_id");
		    var loading = '<div class="text-center waiting"><i class="fas fa-spinner fa-spin blue text-center" style="font-size:40px"></i></div>';
		    $("#update_contact_modal_body").html(loading);

		    $.ajax({
		        url: base_url+'sms_email_manager/ajax_get_contact_update_info',
		        type: 'POST',
		        data: {table_id:table_id},
		        success:function(response)
		        {
		            $("#update_contact_modal_body").html(response);
		        }
		    })
		});


		$(document).on('click', '#update_contact', function(event) {
		    event.preventDefault();

		    var first_name = $("#updated_first_name").val();
		    var last_name = $("#updated_last_name").val();
		    var contact_email = $("#updated_contact_email").val();
		    var phone_number = $("#updated_phone_number").val();
		    var contact_group_name = $("#updated_contact_group_name").val();

		    if(first_name == "" && last_name == "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Name is Required"); ?>', 'warning');
		        return;
		    }

		    if(contact_email == "" && phone_number == "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Email/Phone number is Required"); ?>', 'warning');
		        return;
		    }

		    if(!validateEmail(contact_email) && contact_email != "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Please provide valid email address"); ?>', 'warning');
		        return;
		    }

		    if(contact_group_name == "" || contact_group_name == null || typeof(contact_group_name) == "undefined")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Contact Group is Required"); ?>', 'warning');
		        return;
		    }

		    $(this).addClass('btn-progress')
		    var that = $(this);


		    var alldatas = new FormData($("#contact_update_form")[0]);

		    $.ajax({
		        url: base_url+'sms_email_manager/ajax_update_contact',
		        type: 'POST',
		        dataType: 'JSON',
		        data: alldatas,
		        cache: false,
		        contentType: false,
		        processData: false,
		        success:function(response)
		        {
		            $(that).removeClass('btn-progress');

		            if(response.status == "1")
		            {
		                iziToast.success({title: '',message: response.msg,position: 'bottomRight'});

		            } else 
		            {
		                iziToast.error({title: '',message: response.msg,position: 'bottomRight'});
		            }

		        }
		    })

		});

		var Doyouwanttodeletethisrecordfromdatabase = "<?php echo $this->lang->line('Do you want to detete this record?'); ?>";
		$(document).on('click','.delete_contact',function(e){
		    e.preventDefault();
		    swal({
		        title: '<?php echo $this->lang->line("Are you sure?"); ?>',
		        text: Doyouwanttodeletethisrecordfromdatabase,
		        icon: 'warning',
		        buttons: true,
		        dangerMode: true,
		    })
		    .then((willDelete) => {
		        if (willDelete) 
		        {
		            var table_id = $(this).attr('table_id');

		            $.ajax({
		                context: this,
		                type:'POST' ,
		                url:"<?php echo base_url('sms_email_manager/delete_contact')?>",
		                data:{table_id:table_id},
		                success:function(response)
		                { 
		                    if(response == '1')
		                    {
		                        iziToast.success({title: '',message: '<?php echo $this->lang->line('Contact Group has been deleted successfully.'); ?>',position: 'bottomRight',timeout: 3000});
		                    } else
		                    {
		                        iziToast.error({title: '',message: '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>',position: 'bottomRight',timeout: 3000});
		                    }
		                    table1.draw();
		                }
		            });
		        } 
		    });
		});


		$(document).on('click', '#export_contact', function(event) {
		    event.preventDefault();

		    var contact_ids = [];
		    $(".datatableCheckboxRow:checked").each(function ()
		    {
		        contact_ids.push(parseInt($(this).val()));
		    });
		    
		    if(contact_ids.length==0) 
		    {
		        swal('<?php echo $this->lang->line("Warning")?>', '<?php echo $this->lang->line("You have to select Contacts to export.") ?>', 'warning');
		        return false;
		    }
		    else  
		    {
		        $(this).addClass('btn-progress');
		        $.ajax({
		            context: this,
		            type:'POST',
		            url: base_url+"sms_email_manager/ajax_export_contacts",
		            data:{info:contact_ids},
		            success:function(response){
		                $(this).removeClass('btn-progress');
		                if(response != '')
		                {
		                    var download_url = base_url+response;
		                    window.location.assign(download_url);
		                }
		            }
		        });
		    }
		});


		$(document).on('click', '#import_contact', function(event) {
		    event.preventDefault();
		    $("#import_contacts_modal").modal();
		});

		Dropzone.autoDiscover = false;
		$("#dropzone").dropzone({ 
		    url: "<?php echo site_url();?>sms_email_manager/ajax_import_csv_files",
		    maxFilesize:25,
		    uploadMultiple:false,
		    paramName:"file",
		    createImageThumbnails:true,
		    acceptedFiles: ".csv",
		    maxFiles:1,
		    addRemoveLinks:true,
		    success:function(file, response){
		        $("#csv_file").val(eval(response));
		    },
		    removedfile: function(file) {
		        var name = $("#csv_file").val();
		        if(name !="")
		        {
		            $(".dz-preview").remove();
		            $.ajax({
		                type: 'POST',
		                url: '<?php echo site_url();?>sms_email_manager/delete_uploaded_csv_file',
		                data: {op: "delete",name: name},
		                success: function(data){
		                    $("#csv_file").val('');
		                }
		            });
		        }
		        else
		        {
		            $(".dz-preview").remove();
		        }

		    },
		});

		$(document).on('click', '#upload_imported_csv', function(event) {
		    event.preventDefault();

		    var contact_group = $("#csv_group_id").val();
		    var csvFile = $("#csv_file").val();

		    if(contact_group == "" || contact_group == null || typeof(contact_group) == "undefined")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Contact Group is Required."); ?>', 'warning');
		        return;
		    }

		    if(csvFile == "")
		    {
		        swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Please Upload Contact CSV file."); ?>', 'warning');
		        return;
		    }

		    $(this).addClass('btn-progress');
		    var that = $(this);

		    var imported_files_data = new FormData($("#import_contact_csv")[0]);

		    $.ajax({
		        url: base_url+'sms_email_manager/import_contact_action_ajax',
		        type: 'POST',
		        data: imported_files_data,
		        dataType:'json',
		        async: false,
		        cache: false,
		        contentType: false,
		        processData: false,
		        success:function(response){
		            $(that).removeClass('btn-progress');
		            var report_link = base_url+"sms_email_manager/contact_list";
		            if(response.status=='ok')
		            {    
		                var total = response.count;
		                console.log(total);
		                var span = document.createElement("span");
		                if(total == "0")
		                	span.innerHTML = '<b>'+total+'</b> '+'<?php echo $this->lang->line("contacts has been imported from csv beacause of your given contacts information already exists in the database."); ?>';
		                else
		                	span.innerHTML = '<b>'+total+'</b> '+'<?php echo $this->lang->line("contacts has been imported from csv was successfully"); ?>';

		                swal({ title:'<?php echo $this->lang->line("Imported"); ?>', content:span,icon:'success'}).then((value) => {window.location.href=report_link;});
		            }
		            else
		            {
		                var error = response.status.replace(/<\/?[^>]+(>|$)/g, "");
		                var span = document.createElement("span");
		                span.innerHTML = error;
		                swal({ title:'<?php echo $this->lang->line("Error"); ?>', content:span,icon:'error'}).then((value) => {window.location.href=report_link;});
		            }

		        }
		    });
		    
		});

		
		$("#add_contact_form_modal").on('hidden.bs.modal', function ()
		{
		    $("#contact_add_form").trigger('reset');
		    $("#contact_group_name").change();
		    table1.draw();
		});

		$("#update_contact_form_modal,#import_contacts_modal").on('hidden.bs.modal', function ()
		{
		    table1.draw();
		});

		$("#import_contacts_modal").on('hidden.bs.modal', function ()
		{
		    $("#csv_group_id").val("").change();
		    $(".dz-remove").click();
		    $("#csv_file").val("");
		    table1.draw();
		});


		$("#add_contact_group_modal,#update_contact_group_modal").on('hidden.bs.modal', function ()
		{
		    location.reload();
		});
		
	});
</script>