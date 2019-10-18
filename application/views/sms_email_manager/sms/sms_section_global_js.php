<script>
    $(document).ready(function($) {

        var base_url = '<?php echo base_url(); ?>';


        var today = new Date();
        var next_date = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());
        $('.datepicker_x').datetimepicker({
            theme:'light',
            format:'Y-m-d H:i:s',
            formatDate:'Y-m-d H:i:s',
            minDate: today,
            maxDate: next_date

        })

        $('[data-toggle=\"tooltip\"]').tooltip();

        // =========================== SMS API Section started and datatable section started ========================
        var perscroll;
        var table = $("#mytable").DataTable({
            serverSide: true,
            processing:true,
            bFilter: false,
            order: [[ 1, "desc" ]],
            pageLength: 10,
            ajax: 
            {
              "url": base_url+'sms_email_manager/sms_api_list_data',
              "type": 'POST',
              data: function ( d )
              {
                  d.searching = $('#searching').val();
              }
            },
            language: 
            {
              url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            columnDefs: [
                {
                  targets: [1],
                  visible: false
                },
                {
                  targets: '',
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
        // End of datatable section

        $(document).on('click', '.see_api_details', function(event) {
        	event.preventDefault();

        	var table_id = $(this).attr("table_id");
        	$("#api_info").modal();

        	var loading = '<div class="text-center waiting"><i class="fas fa-spinner fa-spin blue text-center" style="font-size:40px"></i></div>';

        	$("#api_info_modal_body").hide();
        	$("#info_body").append(loading);

        	$.ajax({
        		url: base_url+'sms_email_manager/api_infos',
        		type: 'POST',
        		dataType:'json',
        		data: {table_id: table_id},
        		success:function(response)
        		{
        			$(".waiting").remove();
        			$("#api_info_modal_body").show();

        			$("#auth_id_val").html(response.username_auth_id);
        			$("#api_secret_val").html(response.password_auth_token);
        			$("#api_id_val").html(response.api_id);
        			$("#remaining_credits_val").html(response.remaining_credetis);

        		}
        	})
        	
        });

        $(document).on('click', '.add_gateway', function(event) {
        	event.preventDefault();
        	$("#add_sms_api_form_modal").modal();
        });

        $(document).on('click', '#save_api', function(event) {
        	event.preventDefault();
        	
        	var gateway_name = $("#gateway_name").val();
        	if(gateway_name == "")
        	{
        		swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Gateway is Required"); ?>', 'warning');
        		return;
        	}

        	$(this).addClass('btn-progress')
        	var that = $(this);

        	var alldatas = new FormData($("#sms_api_form")[0]);

        	$.ajax({
        		url: base_url+'sms_email_manager/ajax_create_sms_api',
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

        $(document).on('click', '.edit_api', function(event) {
        	event.preventDefault();
        	$("#update_sms_api_form_modal").modal();
        	var table_id = $(this).attr("table_id");
            var loading = '<div class="text-center waiting"><i class="fas fa-spinner fa-spin blue text-center" style="font-size:40px"></i></div>';
            $("#updated_form_modal_body").html(loading);

        	$.ajax({
        		url: base_url+'sms_email_manager/ajax_get_api_info_for_update',
        		type: 'POST',
        		data: {table_id: table_id},
        		success:function(response)
        		{
                    if(response)
                        $("#updated_form_modal_body").html(response);
                    else
                        $("#updated_form_modal_body").html(loading);
        		}     
        	})
        });

        $(document).on('click', '#update_api', function(event) {
            event.preventDefault();

            var gateway_name = $("#updated_gateway_name").val();
            if(gateway_name == "")
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("Gateway is Required"); ?>', 'warning');
                return;
            }

            $(this).addClass('btn-progress')
            var that = $(this);

            var updated_data = new FormData($("#update_sms_api_form")[0]);

            $.ajax({
                url: base_url+'sms_email_manager/ajax_update_sms_api',
                type: 'POST',
                dataType: 'JSON',
                data: updated_data,
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
        $(document).on('click','.delete_api',function(e){
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
                        url:"<?php echo base_url('sms_email_manager/delete_sms_api')?>",
                        data:{table_id:table_id},
                        success:function(response){ 

                            if(response == '1')
                            {
                                iziToast.success({title: '',message: '<?php echo $this->lang->line('API has been Deleted Successfully.'); ?>',position: 'bottomRight'});
                                table.draw();
                            } else
                            {
                                iziToast.error({title: '',message: '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>',position: 'bottomRight'});
                            }
                        }
                    });
                } 
            });
        });

        $(document).on('click', '#instruction_guide', function(event) {
        	event.preventDefault();
        	$("#instruction_guide_modal").modal();
        });

        $('#instruction_guide_modal').on("hidden.bs.modal", function (e) { 
            if ($('.modal:visible').length) { $('body').addClass('modal-open'); }
        });

        $("#api_info").on('hidden.bs.modal', function ()
        {
            $("#auth_id_val").html("");
            $("#api_secret_val").html("");
            $("#api_id_val").html("");
            $("#remaining_credits_val").html("");
            table.draw();
        });    


        $("#add_sms_api_form_modal").on('hidden.bs.modal', function ()
        {
            $("#sms_api_form").trigger('reset');
            $("#gateway_name").change();
            table.draw();
        });

        $("#update_sms_api_form_modal").on('hidden.bs.modal', function ()
        {
            table.draw();
        });

        // SMS API section ended here

        // ======================================================= SMS Campaign JS SEction ========================================

        var base_url = '<?php echo base_url(); ?>';
        var somethingwentwrong = '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>';

        $(".schedule_block_item").hide();

        setTimeout(function(){ 
          $('#post_date_range').daterangepicker({
            ranges: {
              '<?php echo $this->lang->line("Last 30 Days");?>': [moment().subtract(29, 'days'), moment()],
              '<?php echo $this->lang->line("This Month");?>'  : [moment().startOf('month'), moment().endOf('month')],
              '<?php echo $this->lang->line("Last Month");?>'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment()
          }, function (start, end) {
            $('#post_date_range_val').val(start.format('YYYY-M-D') + '|' + end.format('YYYY-M-D')).change();
          });
        }, 2000);

        var perscroll_sms;
        var table_sms = $("#mytable_sms_campaign").DataTable({
            serverSide: true,
            processing:true,
            bFilter: false,
            order: [[ 1, "desc" ]],
            pageLength: 10,
            ajax: 
            {
                "url": base_url+'sms_email_manager/sms_campaign_lists_data',
                "type": 'POST',
                data: function ( d )
                {
                    d.campaign_status = $('#campaign_status').val();
                    d.post_date_range = $('#post_date_range_val').val();
                    d.searching_campaign = $('#searching_campaign').val();
                }
            },
            language: 
            {
              url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            columnDefs: [
                {
                  targets: [1],
                  visible: false
                },
                {
                  targets: [0,1,4,5,6,7,8],
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
                if (perscroll_sms) perscroll_sms.destroy();
                perscroll_sms = new PerfectScrollbar('#mytable_sms_campaign_wrapper .dataTables_scrollBody');
              }
            },
            scrollX: 'auto',
            fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
              if(areWeUsingScroll)
              { 
                if (perscroll_sms) perscroll_sms.destroy();
                perscroll_sms = new PerfectScrollbar('#mytable_sms_campaign_wrapper .dataTables_scrollBody');
              }
            }
        });


        $(document).on('change', '#campaign_status', function(event) {
          event.preventDefault(); 
          table_sms.draw();
        });

        $(document).on('change', '#post_date_range_val', function(event) {
          event.preventDefault(); 
          table_sms.draw();
        });

        $(document).on('click', '#sms_search_submit', function(event) {
          event.preventDefault(); 
          table_sms.draw();
        });
        // // End of datatable section

        var Doyouwanttodeletethisrecordfromdatabase = "<?php echo $this->lang->line('Do you want to detete this record?'); ?>";
        $(document).on('click','.delete_sms_campaign',function(e){
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
                    var campaign_id = $(this).attr('id');

                    $.ajax({
                        context: this,
                        type:'POST' ,
                        url:"<?php echo base_url('sms_email_manager/delete_sms_campaign')?>",
                        data:{campaign_id:campaign_id},
                        success:function(response)
                        { 
                            if(response == '1')
                            {
                                iziToast.success({title: '',message: '<?php echo $this->lang->line('Campaign has been deleted successfully.'); ?>',position: 'bottomRight'});
                            } else
                            {
                                iziToast.error({title: '',message: '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>',position: 'bottomRight'});
                            }

                            table_sms.draw();
                        }
                    });
                } 
            });
        });



        /** Including variables on click **/
        $(document).on('click','#contact_first_name',function(){ 
            var $txt = $("#message");
            var caretPos = $txt[0].selectionStart;

            var textAreaTxt = $txt.val();
            var txtToAdd = " #FIRST_NAME# ";
            $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
        });

        $(document).on('click','#contact_last_name',function(){ 
            var $txt = $("#message");
            var caretPos = $txt[0].selectionStart;
            var textAreaTxt = $txt.val();
            var txtToAdd = " #LAST_NAME# ";
            $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
        });

        $(document).on('click','#contact_mobile_number',function(){  
            var $txt = $("#message");
            var caretPos = $txt[0].selectionStart;
            var textAreaTxt = $txt.val();
            var txtToAdd = " #MOBILE# ";
            $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
        });

        $(document).on('click','#contact_email_address',function(){  
            var $txt = $("#message");
            var caretPos = $txt[0].selectionStart;
            var textAreaTxt = $txt.val();
            var txtToAdd = " #EMAIL_ADDRESS# ";
            $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
        });
        /** End of Including variables by click **/

        $(document).on('change', '#country_code_action', function(event) {
            event.preventDefault();
            /* Act on the event */
            var action = $(this).val();
            var country_code = $("#country_code").val();

            if(action == "1")
            {
                $("#country_code_add").val(country_code);
                if($("#country_code_remove").val() !='')
                {
                    $("#country_code_remove").val("");
                }
            }
            if(action=='0')
            {
                $("#country_code_remove").val(country_code);
                if($("#country_code_add").val() !='')
                {
                    $("#country_code_add").val("");
                }
            }

        });

        Dropzone.autoDiscover = false;
        $("#dropzone").dropzone({ 
            url: "<?php echo site_url();?>sms_email_manager/ajax_campaign_import_csv_files",
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

        // import csv section
        $("#import_submit").click(function(){      
            var fileval = $("#csv_file").val();
            if(fileval=="")
                swal('<?php echo $this->lang->line("Warning"); ?>', '<?php echo $this->lang->line("No file selected, Please upload a file.");?>', 'warning');
            else
            {  
                $(this).addClass('btn-progress')
                var that = $(this);

                $.ajax({
                    url: base_url+'sms_email_manager/generating_numbers',
                    type: 'POST',
                    data: {fileval:fileval},
                    dataType:'json',
                    success: function (response)                
                    {
                        $(that).removeClass('btn-progress');   
                        if(response.status=='1')
                        {               
                            var file_content = response.file;
                            var to_numbers = $("#to_numbers").val().trim();
                            if(to_numbers != "") file_content = ','+file_content;   
                            file_content = to_numbers + file_content;
                            var totalNumbers = file_content.split(",").length;
                            $("#manual_numbers").html('<i class="fas fa-spinner fa-spin"></i>');
                            $("#manual_numbers").html(totalNumbers);
                            $("#to_numbers").val(file_content);
                            $("#csv_import_modal").modal('hide');
                            iziToast.success({title: '',message: '<?php echo $this->lang->line("import from csv was successful")?>',position: 'bottomRight'});
                        }
                        else
                        {
                            var error=response.status.replace(/<\/?[^>]+(>|$)/g, "");
                            iziToast.error({title: '',message: error,position: 'bottomRight'});
                        }
                    }
                });
            }         
                 
        });

        $("#csv_import_modal").on("hidden.bs.modal",function(){
            $("#csv_file").val("");
            $(".dz-remove").click();
        });

        
        $(document).on('change','input[name=schedule_type]',function(){
            var schedule_type = $("input[name=schedule_type]:checked").val();

            if(typeof(schedule_type)=="undefined"){
                $(".schedule_block_item").show();
            }
            else
            {
                $("#schedule_time").val("");
                $("#time_zone").val("");
                $(".schedule_block_item").hide();
            }
        });

        $(document).on('click','#create_campaign',function(){

            var campaign_name = $("#campaign_name").val();
            var message       = $("#message").val();
            var contacts_id   = $("#contacts_id").val();
            var to_numbers    = $("#to_numbers").val().trim();
            var from_sms      = $("#from_sms").val();
            var schedule_type = $("input[name=schedule_type]:checked").val();
            var schedule_time = $("#schedule_time").val();
            var time_zone     = $("#time_zone").val();
            var page_name     = $("#page").val();

            // campaign name
            if(campaign_name =='')
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please give a campaign name"); ?>", 'warning');
                return;
            }

            // sms api select
            if(from_sms =='')
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please select a SMS API"); ?>", 'warning');
                return;
            }

            // write message
            if(message =='')
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please write your message"); ?>", 'warning');
                return;
            }

            if(page_name == "" && contacts_id == "" && to_numbers == "")
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please Select Page name or Contact Group."); ?>", 'warning');
                return;
            }


            // if schedule is later
            if(typeof(schedule_type)=='undefined' && (schedule_time=="" || time_zone==""))
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please select schedule time/time zone."); ?>", 'warning');
                return;
            }

            $(this).addClass('btn-progress');
            var that = $(this);

            var report_link = base_url+"sms_email_manager/sms_campaign_lists";
            var success_message = "<?php echo $this->lang->line('Campaign have been submitted successfully.'); ?> <a href='"+report_link+"'><?php echo $this->lang->line('See report here.'); ?></a>";

            var queryString = new FormData($("#sms_campaign_form")[0]);
            
            $.ajax({
                url:base_url+'sms_email_manager/create_sms_campaign_action',
                type:'POST',
                data: queryString,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                success:function(response)
                {
                    $(that).removeClass('btn-progress');

                    if(response.status=='1')
                    {
                      var span = document.createElement("span");
                      span.innerHTML = success_message;
                      swal({ title:'<?php echo $this->lang->line("Campaign Submitted"); ?>', content:span,icon:'success'}).then((value) => {window.location.href=report_link;});
                    }
                    else 
                        swal('<?php echo $this->lang->line("Error"); ?>', response.message, 'error').then((value) => {window.location.href=report_link;});
                }
            });
        
        });


        $("#update_sms_campaign_btn").click(function()
        {
            var campaign_name = $("#campaign_name").val();
            var message       = $("#message").val();
            var contacts_id   = $("#contacts_id").val();
            var to_numbers    = $("#to_numbers").val().trim();
            var from_sms      = $("#from_sms").val();
            var schedule_type = $("input[name=schedule_type]:checked").val();
            var schedule_time = $("#schedule_time").val();
            var time_zone     = $("#time_zone").val();
            var page_name     = $("#page").val();

            // campaign name
            if(campaign_name =='')
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please give a campaign name"); ?>", 'warning');
                return;
            }

            // sms api select
            if(from_sms =='')
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please select a SMS API"); ?>", 'warning');
                return;
            }

            // write message
            if(message =='')
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please write your message"); ?>", 'warning');
                return;
            }

            if(page_name == "" && contacts_id == "" && to_numbers == "")
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please Select Page name or Contact Group."); ?>", 'warning');
                return;
            }

            // if schedule is later
            if(typeof(schedule_type)=='undefined' && (schedule_time=="" || time_zone==""))
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please select schedule time/time zone."); ?>", 'warning');
                return;
            }

            $(this).addClass('btn-progress');
            var that = $(this);

            var report_link = base_url+"sms_email_manager/sms_campaign_lists";
            var success_message = "<?php echo $this->lang->line('Campaign have been updated successfully.'); ?> <a href='"+report_link+"'><?php echo $this->lang->line('See report here.'); ?></a>";

            var queryString = new FormData($("#updated_sms_campaign_form")[0]);
            
            $.ajax({
                url:base_url+'sms_email_manager/edit_sms_campaign_action',
                type:'POST',
                data: queryString,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                success:function(response)
                {
                    $(that).removeClass('btn-progress');

                    if(response.status=='1')
                    {
                      var span = document.createElement("span");
                      span.innerHTML = success_message;
                      swal({ title:'<?php echo $this->lang->line("Campaign Submitted"); ?>', content:span,icon:'success'}).then((value) => {window.location.href=report_link;});
                    }
                    else 
                        swal('<?php echo $this->lang->line("Error"); ?>', response.message, 'error').then((value) => {window.location.href=report_link;});
                }
            });
        
        });


        // report table started
        var table_campaign_report = '';
        var perscroll_campaign_report;
        $(document).on('click','.campaign_report',function(e){
          e.preventDefault();

            var table_id = $(this).attr('table_id');
            if(table_id !='') $("#put_row_id").val(table_id);

            var campaignName     = $(this).attr("campaign_name");
            var sms_api_name     = $(this).attr("send_as");
            var successfullysent = $(this).attr("successfullysent");
            var totalThread      = $(this).attr("totalThread");
            var campaignMessage  = $(this).attr("campaign_message");
                campaignMessage  = campaignMessage.replace(/(\r\n|\n\r|\r|\n)/g, "<br>")
            var campaignStatus   = $(this).attr("campaign_status");
            
            $("#restart_button").hide();

            var posting_status = '';
            if(campaignStatus == '0') posting_status = 'Pending';
            if(campaignStatus == '1') posting_status = 'Processing';
            if(campaignStatus == '2') posting_status = 'Completed';
            if(campaignStatus == '3') {
                posting_status = 'Paused';
                $("#restart_button").show();
            }

            if(campaignStatus == '2') $("#options_div").hide();


            $("#sms_campaign_name").html(campaignName);
            $("#api_name").html(sms_api_name);
            $("#posting_status").html(posting_status);
            $("#sent_state").html(successfullysent+'/'+totalThread);
            $("#original_message").html(campaignMessage);

            $("#edit_content").attr("href",base_url+"sms_email_manager/edit_campaign_content/"+table_id);
            $("#restart_button").attr("table_id",table_id);

            $("#campaign_report_modal").modal();

            setTimeout(function(){
                if (table_campaign_report == '')
                {
                    table_campaign_report = $("#mytable_campaign_report").DataTable({
                        serverSide: true,
                        processing:true,
                        bFilter: false,
                        order: [[ 3, "desc" ]],
                        pageLength: 10,
                        ajax: {
                            url: base_url+'sms_email_manager/ajax_get_sms_campaign_report_info',
                            type: 'POST',
                            data: function ( d )
                            {
                                d.table_id = $("#put_row_id").val();
                                d.searching = $("#report_search").val();
                            }
                        },
                        language: 
                        {
                          url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
                        },
                        dom: '<"top"f>rt<"bottom"lip><"clear">',
                        columnDefs: [
                          {
                              targets: [0,4],
                              className: 'text-center'
                          },
                          {
                              targets: [0,1,2,4],
                              sortable: false
                          }
                        ],
                        fnInitComplete:function(){ // when initialization is completed then apply scroll plugin
                        if(areWeUsingScroll)
                        {
                            if (perscroll_campaign_report) perscroll_campaign_report.destroy();
                                perscroll_campaign_report = new PerfectScrollbar('#mytable_campaign_report_wrapper .dataTables_scrollBody');
                        }
                        },
                        scrollX: 'auto',
                        fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
                            if(areWeUsingScroll)
                            { 
                            if (perscroll_campaign_report) perscroll_campaign_report.destroy();
                            perscroll_campaign_report = new PerfectScrollbar('#mytable_campaign_report_wrapper .dataTables_scrollBody');
                            }
                        }
                    });
                }
                else table_campaign_report.draw();
            },1000);
        });

        $(document).on('keyup', '#report_search', function(event) {
          event.preventDefault(); 
          table_campaign_report.draw();
        });

        $('#campaign_report_modal').on('hidden.bs.modal', function () {
            $("#put_row_id").val('');
            $("#report_search").val("");
            $("#sms_campaign_name").html("");
            $("#api_name").html("");
            $("#posting_status").html("");
            $("#original_message").html('');
            $("#sent_state").html("");
            $("#edit_content").attr("href","");
            table_campaign_report.draw();
            table_sms.draw();
        });


        $(document).on('click', '#updateMessage', function(event) {
            event.preventDefault();

            var table_id = $("#table_id").val();
            var message = $("#message").val();
            if(message == "")
            {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line("Please type a message. System can not send blank message."); ?>", 'warning');
                return;

            }

            $(this).addClass('btn-progress');
            var that = $(this);

            var queryString = new FormData($("#edit_message_form")[0]);
            $.ajax({
                type:'POST' ,
                url: base_url+"sms_email_manager/edit_campaign_content_action",
                data: queryString,
                cache: false,
                contentType: false,
                processData: false,
                success:function(response)
                { 
                    $(that).removeClass('btn-progress');
                    var report_link = base_url+"sms_email_manager/sms_campaign_lists";
                    if(response == "1")
                    {
                        swal({ title:'<?php echo $this->lang->line("Campaign Updated"); ?>', content:'<?php echo $this->lang->line("Campaign have been updated successfully."); ?>',icon:'success'}).then((value) => {window.location.href=report_link;});
                    }
                }
            });

        });

        // restart the camapaign where it is left
        $(document).on('click','.restart_button',function(e){
            e.preventDefault();
            var table_id = $(this).attr('table_id');

            swal({
                title: '<?php echo $this->lang->line("Force Resume"); ?>',
                text: '<?php echo $this->lang->line('Do you want to resume this campaign?') ?>',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) 
                {
                    $(this).parent().prev().addClass('btn-progress btn-primary').removeClass('btn-outline-primary');
                    $.ajax({
                       context: this,
                       type:'POST' ,
                       url: "<?php echo base_url('sms_email_manager/restart_campaign')?>",
                       data: {table_id:table_id},
                       success:function(response)
                       {
                            $(this).parent().prev().removeClass('btn-progress btn-outline-primary').addClass('btn-primary');
                            if(response=='1') 
                            {
                                $("#campaign_report_modal").modal('hide');
                                iziToast.success({title: '',message: '<?php echo $this->lang->line("Campaign has been resumed by force successfully."); ?>',position: 'bottomRight'});
                                table_sms.draw();
                                table_campaign_report.draw();
                            }       
                            else iziToast.error({title: '',message: somethingwentwrong,position: 'bottomRight'});
                       }
                    });
                } 
            });

        });

        $(document).on('click','.pause_campaign_info',function(e){
            e.preventDefault();
            var table_id = $(this).attr('table_id');

            swal({
                title: '<?php echo $this->lang->line("Pause Campaign"); ?>',
                text: '<?php echo $this->lang->line("Do you want to pause this campaign?"); ?>',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) 
                {
                    $(this).parent().prev().addClass('btn-progress btn-primary').removeClass('btn-outline-primary');
                    $.ajax({
                       context: this,
                       type:'POST' ,
                       url: "<?php echo base_url('sms_email_manager/ajax_sms_campaign_pause')?>",
                       data: {table_id:table_id},
                       success:function(response)
                       {
                            $(this).parent().prev().removeClass('btn-progress btn-primary').addClass('btn-outline-primary');

                            if(response=='1') 
                            {
                                iziToast.success({title: '',message: '<?php echo $this->lang->line("Campaign has been paused successfully."); ?>',position: 'bottomRight'});
                                table_sms.draw();
                            }       
                            else iziToast.error({title: '',message: somethingwentwrong,position: 'bottomRight'});
                       }
                    });
                } 
            });

        });

        $(document).on('click','.play_campaign_info',function(e){
            e.preventDefault();
            var table_id = $(this).attr('table_id');
            var somethingwentwrong = '<?php echo $this->lang->line('Something went wrong, please try once again.'); ?>';

            swal({
                title: '<?php echo $this->lang->line("Resume Campaign"); ?>',
                text: '<?php echo $this->lang->line("Do you want to start this campaign?"); ?>',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) 
                {
                    $(this).parent().prev().addClass('btn-progress btn-primary').removeClass('btn-outline-primary');
                    $.ajax({
                       context: this,
                       type:'POST' ,
                       url: "<?php echo base_url('sms_email_manager/ajax_sms_campaign_play')?>",
                       data: {table_id:table_id},
                       success:function(response)
                       {
                            $(this).parent().prev().removeClass('btn-progress btn-primary').addClass('btn-outline-primary');

                            if(response=='1') 
                            {
                                iziToast.success({title: '',message: '<?php echo $this->lang->line("Campaign has been resumed successfully."); ?>',position: 'bottomRight'});
                                table_sms.draw();
                            }       
                            else iziToast.error({title: '',message: somethingwentwrong,position: 'bottomRight'});
                       }
                    });
                } 
            });

        });

        $(document).on('click','.force',function(e){
            e.preventDefault();
            var id = $(this).attr('id');
            var alreadyEnabled = "<?php echo $this->lang->line("This campaign is already enabled for processing."); ?>";
            var doyoureallywanttoReprocessthiscampaign = "<?php echo $this->lang->line("Force Reprocessing means you are going to process this campaign again from where it ended. You should do only if you think the campaign is hung for long time and didn't send message for long time. It may happen for any server timeout issue or server going down during last attempt or any other server issue. So only click OK if you think message is not sending. Are you sure to Reprocessing ?"); ?>";

            swal({
                title: '<?php echo $this->lang->line("Force Re-process Campaign"); ?>',
                text: doyoureallywanttoReprocessthiscampaign,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) 
                {
                    $(this).parent().prev().addClass('btn-progress btn-primary').removeClass('btn-outline-primary');
                    $.ajax({
                       context: this,
                       type:'POST' ,
                       url: "<?php echo base_url('sms_email_manager/force_reprocess_sms_campaign')?>",
                       data: {id:id},
                       success:function(response)
                       {
                            $(this).parent().prev().removeClass('btn-progress btn-primary').addClass('btn-outline-primary');

                            if(response=='1') 
                            {
                                iziToast.success({title: '',message: '<?php echo $this->lang->line("Campaign has been re-processed by force successfully."); ?>',position: 'bottomRight'});
                                table_sms.draw();
                            }       
                            else iziToast.error({title: '',message: alreadyEnabled,position: 'bottomRight'});
                       }
                    });
                } 
            });

        });

    // =================================================== SMS Report Section ===============================================

        setTimeout(function(){ 
          $('#sms_date_range').daterangepicker({
            ranges: {
              '<?php echo $this->lang->line("Last 30 Days");?>': [moment().subtract(29, 'days'), moment()],
              '<?php echo $this->lang->line("This Month");?>'  : [moment().startOf('month'), moment().endOf('month')],
              '<?php echo $this->lang->line("Last Month");?>'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment()
          }, function (start, end) {
            $('#sms_date_range_val').val(start.format('YYYY-M-D') + '|' + end.format('YYYY-M-D')).change();
          });
        }, 2000);

        // sms logs
        setTimeout(function(){ 
          $('#sms_log_date_range').daterangepicker({
            ranges: {
              '<?php echo $this->lang->line("Last 30 Days");?>': [moment().subtract(29, 'days'), moment()],
              '<?php echo $this->lang->line("This Month");?>'  : [moment().startOf('month'), moment().endOf('month')],
              '<?php echo $this->lang->line("Last Month");?>'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment()
          }, function (start, end) {
            $('#sms_log_date_range_val').val(start.format('YYYY-M-D') + '|' + end.format('YYYY-M-D')).change();
          });
        }, 2000);


        // for sms whole report count(depricated now)
        // section deprecated
        var perscroll_sms_report;
        var table_sms_report = $("#mytable_sms_report").DataTable({
            serverSide: true,
            processing:true,
            bFilter: false,
            order: [[ 5, "desc" ]],
            pageLength: 10,
            ajax: 
            {
              "url": base_url+'sms_email_manager/sms_reports_data',
              "type": 'POST',
              data: function ( d )
              {
                  d.sms_date_range = $('#sms_date_range_val').val();
              }
            },
            language: 
            {
              url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            columnDefs: [
                {
                  targets: '',
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
                if (perscroll_sms_report) perscroll_sms_report.destroy();
                perscroll_sms_report = new PerfectScrollbar('#mytable_sms_report_wrapper .dataTables_scrollBody');
              }
            },
            scrollX: 'auto',
            fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
              if(areWeUsingScroll)
              { 
                if (perscroll_sms_report) perscroll_sms_report.destroy();
                perscroll_sms_report = new PerfectScrollbar('#mytable_sms_report_wrapper .dataTables_scrollBody');
              }
            }
        });

        $(document).on('change', '#sms_date_range_val', function(event) {
          event.preventDefault(); 
          table_sms_report.draw();
        });

        $(document).on('click', '#download_sms_reports', function(event) {
            event.preventDefault();

            var sms_date_range = $("#sms_date_range_val").val();

            $(this).addClass('btn-progress');
            var that = $(this);

            $.ajax({
                url: base_url+"sms_email_manager/download_sms_report",
                type: 'POST',
                dataType: 'JSON',
                data: {sms_date_range: sms_date_range},
                success:function(response){
                    $(that).removeClass('btn-progress');
                    if(response.file_name != ""){
                        var download_link = base_url+response.file_name;
                        window.location.href= download_link;
                        table_sms_report.draw();

                    }
                }

            })
            
        });
        // end section deprecated


        var perscroll_sms_logs;
        table_sms_logs = $("#mytable_sms_logs").DataTable({
            serverSide: true,
            processing:true,
            bFilter: false,
            order: [[ 1, "desc" ]],
            pageLength: 10,
            ajax: 
            {
                "url": base_url+'sms_email_manager/sms_logs_data',
                "type": 'POST',
                data: function ( d )
                {
                    d.sms_logs_date_range = $('#sms_log_date_range_val').val();
                }
            },
            language: 
            {
                url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            columnDefs: [
                {
                  targets: [1],
                  visible: false
                },
                {
                  targets: '',
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
                if (perscroll_sms_logs) perscroll_sms_logs.destroy();
                perscroll_sms_logs = new PerfectScrollbar('#mytable_sms_logs_wrapper .dataTables_scrollBody');
              }
            },
            scrollX: 'auto',
            fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
              if(areWeUsingScroll)
              { 
                if (perscroll_sms_logs) perscroll_sms_logs.destroy();
                perscroll_sms_logs = new PerfectScrollbar('#mytable_sms_logs_wrapper .dataTables_scrollBody');
              }
            }
        });

        $(document).on('change', '#sms_log_date_range_val', function(event) {
          event.preventDefault(); 
          table_sms_logs.draw();
        });


        $(document).on('click', '.see_message', function(event) {
            event.preventDefault();
            
            $("#see_contact_message").modal();
            $("#message_body").html("");
            var msg = $(this).attr("sendMessage");
            var final_msg = msg.replace(/(\r\n|\n\r|\r|\n)/g, "<br>");

            if(final_msg != "") $("#message_body").append('<div class="alert alert-light">'+final_msg+'</div>');
        });

        $("#see_contact_message").on('hidden.bs.modal', function(event) {
            table_sms_logs.draw();
        });

        $(document).on('change','#page,#label_ids,#excluded_label_ids,#user_gender,#user_time_zone,#user_locale',function(){     
            var page_id=$("#page").val();
            var user_gender=$("#user_gender").val();
            var user_time_zone=$("#user_time_zone").val();
            var user_locale=$("#user_locale").val();
            var label_ids=$("#label_ids").val();
            var excluded_label_ids=$("#excluded_label_ids").val();

            if(typeof(label_ids)==='undefined') label_ids = "";
            if(typeof(excluded_label_ids)==='undefined') excluded_label_ids = "";

            var load_label='0';
            if($(this).attr('id')=='page') load_label='1';

            if(load_label=='1')
            {
                $("#dropdown_con").removeClass('hidden');
                $("#first_dropdown").html('<?php echo $this->lang->line("Loading labels..."); ?>');
                $("#second_dropdown").html('<?php echo $this->lang->line("Loading labels..."); ?>');
            }

            $("#page_subscriber").html('<i class="fas fa-spinner fa-spin"></i>');
            $("#targetted_subscriber").html('<i class="fas fa-spinner fa-spin"></i>');

            if(page_id=="")
            {
                $("#page_subscriber,#targetted_subscriber").html("0");
            }

            // $("#submit_post").addClass('btn-progress');

            $.ajax({
                type:'POST' ,
                url: base_url+"sms_email_manager/get_subscribers_phone",
                data: {page_id:page_id,label_ids:label_ids,excluded_label_ids:excluded_label_ids,user_gender:user_gender,user_time_zone:user_time_zone,user_locale:user_locale,load_label:load_label},
                dataType : 'JSON',
                success:function(response){

                    if(load_label=='1')
                    {
                        $("#dropdown_con").removeClass('hidden');
                        $("#first_dropdown").html(response.first_dropdown);
                        $("#second_dropdown").html(response.second_dropdown);
                    }

                    // $("#submit_post").removeClass("btn-progress");

                    $("#page_subscriber").html(response.pageinfo.page_total_subscribers);
                    $("#targetted_subscriber").html(response.pageinfo.subscriber_count);

                    if(load_label=='1')
                    {               
                        if (typeof(xlabels)!=='undefined' && xlabels!="") 
                        {
                            var xlabels_array = xlabels.split(',');
                            $("#label_ids").val(xlabels_array).trigger('change');
                        }
                        if (typeof(xexcluded_label_ids)!=='undefined' && xexcluded_label_ids!="") 
                        {
                            var xexcluded_array = xexcluded_label_ids.split(',');
                            $("#excluded_label_ids").val(xexcluded_array).trigger('change');
                        }
                    }

                    $(".waiting").hide();
                }

            });
        });

        $(document).on('select2:select','#label_ids',function(e){                       
            var label_id = e.params.data.id;
            var temp;

            var excluded_label_ids = $("#excluded_label_ids").val();
            for(var i=0;i<excluded_label_ids.length;i++)
            {
                if(parseInt(excluded_label_ids[i])==parseInt(label_id))
                {
                    temp = "#label_ids option[value='"+label_id+"']";
                    $(temp).prop("selected", false);
                    $("#label_ids").trigger('change');
                    return false;
                }
            }
        });


        $(document).on('select2:select','#excluded_label_ids',function(e){                      
            var label_id = e.params.data.id;
            var temp;

            var label_ids = $("#label_ids").val();
            for(var i=0;i<label_ids.length;i++)
            {
                if(parseInt(label_ids[i])==parseInt(label_id))
                {
                    temp = "#excluded_label_ids option[value='"+label_id+"']";
                    $(temp).prop("selected", false);
                    $("#excluded_label_ids").trigger('change');
                    return false;
                }
            }

        });


        $(document).on('change', '#contacts_id', function(event) {
            event.preventDefault();

            var contact_ids = $("#contacts_id").val();

            $("#contact_numbers").html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: base_url+'sms_email_manager/contacts_total_numbers',
                type: 'POST',
                data: {contact_ids: contact_ids},
                success:function(response){
                    if(response!="")
                        $("#contact_numbers").html(response);
                    else
                        $("#contact_numbers").html("0");


                }
            })

        });

        $(document).on('keyup', '#to_numbers', function(event) {
            event.preventDefault();

            var numbers = $("#to_numbers").val();
            if(numbers != ""){
                numbers = numbers.split(",").length;
                $("#manual_numbers").html('<i class="fas fa-spinner fa-spin"></i>');
                $("#manual_numbers").html(numbers);
            } else
            {
                $("#manual_numbers").html("0");
            }
        });

    });
</script>