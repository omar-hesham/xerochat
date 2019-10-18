<?php include("application/views/sms_email_manager/sms/sms_section_global_js.php"); ?>

<style>
    .activities .activity .activity-detail{width:100%;padding: 0 15px 0 0;box-shadow: none !important;}
    .activity-detail::before { content: none !important; }
    .activity::before{content:none !important;}
    .activities:last-child{border-bottom:none !important;margin-bottom:10px;}
    .dropdown-toggle::after{content:none !important;}
    .dropdown-toggle::before{content:none !important;}
    .bbw{border-bottom-width: thin !important;border-bottom:solid .5px #f9f9f9 !important;padding-bottom:20px;}
    .brbtm{border-bottom:solid .5px #f9f9f9 !important;}
    #searching{max-width: 30% !important;}
    @media (max-width: 575.98px) {#searching{max-width: 77% !important;}}
</style>

<section class="section section_custom">
    <div class="section-header">
        <h1><i class="fas fa-plug"></i> <?php echo $page_title; ?></h1>
        <div class="section-header-button">
            <a class="btn btn-primary add_gateway" href="#">
                <i class="fas fa-plus-circle"></i> <?php echo $this->lang->line("New API"); ?>
            </a> 
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="<?php echo base_url("messenger_bot_broadcast"); ?>"><?php echo $this->lang->line("Broadcasting"); ?></a></div>
            <div class="breadcrumb-item"><?php echo $page_title; ?></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body data-card">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive2">
                                    <table class="table table-bordered" id="mytable">
                                        <thead>
                                            <tr>
                                                <th>#</th>      
                                                <th><?php echo $this->lang->line("ID"); ?></th>      
                                                <th><?php echo $this->lang->line("Gateway"); ?></th>
                                                <th><?php echo $this->lang->line("Sender/ Sender ID/ Mask/ From"); ?></th>
                                                <th><?php echo $this->lang->line("Status"); ?></th>
                                                <th><?php echo $this->lang->line('Actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>            
                    </div>
                </div>
            </div>
        </div>

    </div>
</section> 


<div class="modal fade" id="api_info" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="min-width:50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> <?php echo $this->lang->line('API Informations'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="info_body">
                <div class="card" id="api_info_modal_body">
                    <div class="card-body">
                        <div class="activities">
                            <div class="row">
                                <div class="col-12">
                                    <div class="activity">
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <h5 class="text-job text-primary" id="auth_id_title"><?php echo $this->lang->line('Auth ID/ Auth Key/API Key/ MSISDN/ Account SID/ Account ID/ Username/ Admin'); ?></h5>
                                            </div>
                                            <small id="auth_id_val"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="activity">
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <h5 class="text-job text-primary" id="api_secret_title"><?php echo $this->lang->line('Auth Token/ API Secret/ Password'); ?></h5>
                                            </div>
                                            <small id="api_secret_val"></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="activity">
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <h5 class="text-job text-primary" id="api_id_title"><?php echo $this->lang->line('API ID'); ?></h5>
                                            </div>
                                            <small id="api_id_val"></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="activity">
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <h5 class="text-job text-primary" id="remaining_credits_title"><?php echo $this->lang->line('Remaining Credits'); ?> 
                                                    <small class="text-dark">[plivo, clickatell, clickatell-platform, nexmo, africastalking.com]</small>
                                                </h5>
                                            </div>
                                            <small id="remaining_credits_val"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_sms_api_form_modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-mega">
        <div class="modal-content">
            <div class="modal-header bbw">
                <h5 class="modal-title text-center blue">
                    <i class="fas fa-plus-circle"></i> <?php echo $this->lang->line("New SMS API"); ?>&nbsp;&nbsp;&nbsp;
                    <a href="#" class="btn btn-primary btn-sm" title="<?php echo $this->lang->line("Click to See the Instruction guide on SMS API"); ?>" id="instruction_guide"><i class="fas fa-info-circle" style="font-size: 12px !important;"></i> <?php echo $this->lang->line('Instructions'); ?></a>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">                    
                        <form action="#" enctype="multipart/form-data" id="sms_api_form" method="post">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Gateway Name'); ?></label>
                                        <?php  
                                        $gateway_lists[''] = $this->lang->line("Select Gateway");
                                        echo form_dropdown('gateway_name',$gateway_lists,set_value('gateway_name'),'class="form-control select2" id="gateway_name" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Auth ID/ Auth Key/ API Key/ MSISDN/ Account SID/ Account ID/ Username/ Admin'); ?></label>
                                        <input type="text" class="form-control" name="username_auth_id" id="username_auth_id">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Auth Token/ API Secret/ Password'); ?></label>
                                        <input type="text" class="form-control" name="password_auth_token" id="password_auth_token">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('API ID'); ?></label>
                                        <input type="text" class="form-control" name="api_id" id="api_id">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Sender/ Sender ID/ Mask/ From'); ?></label>
                                        <input type="text" class="form-control" name="phone_number" id="phone_number">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label style="margin-bottom:20px;"><?php echo $this->lang->line('Status'); ?></label><br>
                                        <label class="custom-switch">
                                            <input type="checkbox" name="status" value="1" id="status" class="custom-switch-input" checked>
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description"><?php echo $this->lang->line('Active');?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <div class="col-12 padding-0">
                    <button class="btn btn-primary" id="save_api" name="save_api" type="button"><i class="fas fa-save"></i> <?php echo $this->lang->line("Save") ?> </button>
                    <a class="btn btn-light float-right" data-dismiss="modal" aria-hidden="true"><i class="fas fa-times"></i> <?php echo $this->lang->line("Cancel") ?> </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="update_sms_api_form_modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-mega">
        <div class="modal-content">
            <div class="modal-header bbw">
                <h5 class="modal-title text-center">
                    <i class="fas fa-edit"></i> <?php echo $this->lang->line("Update SMS API"); ?>
                    <a href="#" class="btn btn-primary btn-sm" title="<?php echo $this->lang->line("Click to See the Instruction guide on SMS API"); ?>" id="instruction_guide"><i class="fas fa-info-circle" style="font-size: 12px !important;"></i> <?php echo $this->lang->line('Instructions'); ?></a>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="#">
                <div id="updated_form_modal_body"></div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <div class="col-12 padding-0">
                    <button class="btn btn-primary" id="update_api" name="update_api" type="button"><i class="fas fa-edit"></i> <?php echo $this->lang->line("Update");?></button>
                    <a class="btn btn-light float-right" data-dismiss="modal" aria-hidden="true"><i class="fas fa-times"></i> <?php echo $this->lang->line("Cancel"); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="instruction_guide_modal">
    <div class="modal-dialog" style="min-width:40%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center">
                    <i class="fas fa-plus-circle"></i> <?php echo $this->lang->line("Instructions to configure SMS API"); ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div class="activities">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : Planet IT</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Username, Password, Sender.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : Twilio</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Account Sid, Auth Token, From</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : Plivo</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Auth ID, Auth Token, Sender</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : Clickatell</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API Username, API Password, API ID</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : Clickatell-platform</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API ID</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : Nexmo</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API Key, API Secret, Sender/From</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : msg91.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Auth Key, Sender</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : semysms.net</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Auth Token, API ID [Use devide ID in API ID]</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : africastalking.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API Key, Sender ID/From [Use username in Sender ID/From]</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- deprecated -->
<!--                         <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : textlocal.in</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API Key, Sender</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : sms4connect.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Account ID, Password, Mask</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : telnor.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> MSISDN, Password, From</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : trio-mobile.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API Key, Sender ID</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : routesms.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Username, Password, Sender ID/From</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : sms40.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Username, Password, Sender ID/From</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : infobip.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Username, Password, Sender ID/From</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : smsgateway.me</h5>
                                    </div>
                                    <small><b>Required Fields :</b> API Token, API ID [Use device ID in API ID]</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="activity">
                                <div class="activity-detail">
                                    <div class="mb-2">
                                      <h5 class="text-job text-primary"><i class="fa fa-plug"></i> Gateway : mvaayoo.com</h5>
                                    </div>
                                    <small><b>Required Fields :</b> Admin, Password, Sender ID</small><br>
                                    <small><b>Password format :</b> email:password <i>[i.e. example@example.com:XXXX]</i></small>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <div class="col-12">
                    <button class="btn btn-light float-right" type="button" data-dismiss="modal" aria-hidden="true"><i class="fas fa-times"></i> <?php echo $this->lang->line('Close'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>




