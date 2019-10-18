<?php include("application/views/sms_email_manager/contact_book/contact_book_js.php"); ?>

<style>
	.dropdown-toggle::after{content:none !important;}
	.dropdown-toggle::before{content:none !important;}
	#contact_list_searching{max-width: 100% !important;}
	#group_id{width: 150px !important;}
	.dropzone{min-height:0px !important;}
	.dz-message{margin:65px !important;}
	.bbw{border-bottom-width: thin !important;border-bottom:solid .5px #f9f9f9 !important;padding-bottom:20px;}
	.brTop{border-top:solid .5px #f9f9f9 !important;}
	@media (max-width: 575.98px) {
	#group_id{width: 130px !important;}
	#contact_list_searching{max-width: 77% !important;}
	}
</style>

<section class="section section_custom">
	<div class="section-header">
		<h1><i class="fas fa-book"></i> <?php echo $page_title; ?></h1>
		<div class="section-header-button">
			<a class="btn btn-primary add_new_contact" href="#">
				<i class="fas fa-plus-circle"></i> <?php echo $this->lang->line("New Contact"); ?>
			</a> 
		</div>
		<div class="section-header-breadcrumb">
			<div class="breadcrumb-item">
			    <a href="<?php echo base_url("subscriber_manager"); ?>"><?php echo $this->lang->line("Subscriber Manager"); ?></a>
			</div>
			<div class="breadcrumb-item"><?php echo $page_title; ?></div>
		</div>
	</div>

	<div class="section-body">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body data-card">
						<div class="row">
							<div class="col-md-6 col-12">
								<div class="input-group mb-3 float-left" id="searchbox">
									<!-- search by page name -->
									<div class="input-group-prepend">
										<select class="select2 form-control" id="group_id" name="group_id">
											<option value=""><?php echo $this->lang->line("Contact Group"); ?></option>
											<?php foreach ($contact_group_lists as $key => $value): ?>
												<option value="<?php echo $key; ?>"><?php echo $value;?></option>
											<?php endforeach ?>
										</select>
									</div>
									<input type="text" class="form-control" id="contact_list_searching" name="contact_list_searching" placeholder="<?php echo $this->lang->line('Search...'); ?>" aria-label="" aria-describedby="basic-addon2">
									<div class="input-group-append">
										<button class="btn btn-primary" id="contact_list_search_submit" title="<?php echo $this->lang->line('Search'); ?>" type="button"><i class="fas fa-search"></i> <span class="d-none d-sm-inline"><?php echo $this->lang->line('Search'); ?></span></button>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-12">
								<div class="float-right">
									<a href="#" id="import_contact" class="btn btn-primary btn-lg icon-left btn-icon">
										<i class="fas fa-cloud-upload-alt"></i> <?php echo $this->lang->line("Import");?>
									</a>
									<a href="#" id="export_contact" class="btn btn-danger btn-lg icon-left btn-icon">
										<i class="fas fa-cloud-download-alt"></i> <?php echo $this->lang->line("Export");?>
									</a>
								</div>
							</div>
						</div>
						<div class="table-responsive2">
							<table class="table table-bordered" id="mytable1">
								<thead>
									<tr>
										<th>#</th>
										<th style="vertical-align:middle;width:20px">
										    <input class="regular-checkbox" id="datatableSelectAllRows" type="checkbox"/>
										    <label for="datatableSelectAllRows"></label>        
										</th> 
										<th><?php echo $this->lang->line("Contact ID"); ?></th>      
										<th><?php echo $this->lang->line("First Name"); ?></th>
										<th><?php echo $this->lang->line("Last Name"); ?></th>
										<th><?php echo $this->lang->line("Email"); ?></th>
										<th><?php echo $this->lang->line("Phone"); ?></th>
										<th><?php echo $this->lang->line("Contact Group"); ?></th>
										<th><?php echo $this->lang->line("Actions"); ?></th>
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
</section>


<div class="modal fade" id="add_contact_form_modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="min-width:60%;">
        <div class="modal-content">
            <div class="modal-header bbw">
                <h5 class="modal-title text-center blue">
                    <i class="fas fa-user-plus"></i> <?php echo $this->lang->line("New Contact"); ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">                    
                        <form action="#" enctype="multipart/form-data" id="contact_add_form" method="post">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('First Name'); ?></label>
                                        <input type="text" class="form-control" name="first_name" id="first_name">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Last Name'); ?></label>
                                        <input type="text" class="form-control" name="last_name" id="last_name">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Email'); ?></label>
                                        <input type="email" class="form-control" name="contact_email" id="contact_email">
                                        
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Phone Number'); ?></label>
                                        <input type="text" class="form-control" name="phone_number" id="phone_number">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('Contact Group'); ?>
                                        	<a href="#" data-toggle='tooltip' title="<?php echo $this->lang->line("You Can select multiple contact group."); ?>"><i class="fas fa-info-circle"></i></a>
                                        </label>
                                        <select name="contact_group_name[]" id="contact_group_name" multiple class="form-control select2" style="width:100%;">
											<?php 
											foreach($contact_group_lists as $key => $val)
											{
												echo "<option value='{$key}'>{$val}</option>";
											}
											?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
			<div class="modal-footer bg-whitesmoke">
                <div class="col-12 padding-0">
                    <button class="btn btn-primary" id="save_contact" name="save_contact" type="button"><i class="fas fa-save"></i> <?php echo $this->lang->line("Save") ?> </button>
                    <a class="btn btn-light float-right" data-dismiss="modal" aria-hidden="true"><i class="fas fa-times"></i> <?php echo $this->lang->line("Cancel") ?> </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="update_contact_form_modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="min-width:60%;">
        <div class="modal-content">
            <div class="modal-header bbw">
                <h5 class="modal-title text-center blue">
                    <i class="fas fa-user-edit"></i> <?php echo $this->lang->line("Update Contact"); ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
            	<div id="update_contact_modal_body"></div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <div class="col-12 padding-0">
                    <button class="btn btn-primary" id="update_contact" name="update_contact" type="button"><i class="fas fa-edit"></i> <?php echo $this->lang->line('Update'); ?></button>
                    <a class="btn btn-light float-right" data-dismiss="modal" aria-hidden="true"><i class="fas fa-times"></i> <?php echo $this->lang->line('Cancel'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="import_contacts_modal" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-lg" style="min-width:70%;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-cloud-upload-alt"></i> <?php echo $this->lang->line('Import Contact (CSV)'); ?></h5>&nbsp;&nbsp;&nbsp;
				<a class="btn btn-primary btn-sm" target="_BLANK" href="<?php echo base_url("assets/sample/contact_import_sample.csv"); ?>"><i class="fas fa-download"></i> <?php echo $this->lang->line('Sample CSV'); ?></a>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-12 col-md-6">
						<form action="#" method="POST" id="import_contact_csv" enctype="multipart/form-data">
							<div class="row">
								<div class="col-12 col-md-12">
									<div class="form-group">
										<label><?php echo $this->lang->line('Contact Group'); ?></label>
										<select class="select2 form-control" multiple id="csv_group_id" name="csv_group_id[]" style="width:100%;">
											<?php foreach ($contact_group_lists as $key => $value): ?>
												<option value="<?php echo $key; ?>"><?php echo $value;?></option>
											<?php endforeach ?>
										</select>
									</div>
								</div>

			             		<div class="col-12 col-md-12">
			             			<div class="form-group">
			             				<label class="form-label"> <?php echo $this->lang->line('CSV File') ?>
			             					<a href="#" data-placement="top" data-toggle="popover" title="<?php echo $this->lang->line("Message"); ?>" data-content="<?php echo $this->lang->line("Upload your CSV file. You can see the original format of importing CSV file by downloading our Sample CSV file. Email/Phone number which are already added before will be ignored during importing if CSV file have them."); ?>"><i class='fa fa-info-circle'></i> </a>
			             				</label>
		             				    <div id="dropzone" class="dropzone dz-clickable">
		             				        <div class="dz-default dz-message" style="">
		             				        	<input class="form-control" name="csv_file" id="csv_file" placeholder="" type="hidden">
		             				            <span style="font-size: 20px;"><i class="fas fa-cloud-upload-alt" style="font-size: 35px;color: #6777ef;"></i> <?php echo $this->lang->line('Upload'); ?></span>
		             				        </div>
		             				     </div>
			             			</div>
			             		</div>

			             		<div class="col-12 col-md-12">
			             			<button type="button" class="btn btn-lg btn-primary" id="upload_imported_csv"><i class="fas fa-cloud-upload-alt"></i> <?php echo $this->lang->line('Import'); ?></button>
		             			</div>
							</div>
						</form>
					</div>
					<div class="col-12 col-md-6"><br>
						<div class="alert alert-light alert-has-icon">
							<div class="alert-icon"><i class="far fa-lightbulb"></i></div>
							<div class="alert-body">
								<div class="alert-title"><?php echo $this->lang->line('Message'); ?></div>
								<?php echo $this->lang->line("If you used Microsoft Excel or any other spreadsheet program to fill up your contact CSV then please make sure the values were saved properly by opening the file with notepad or any other text editor. See the below image please."); ?>
								<img src="<?php echo base_url("assets/images/sample.png") ?>" alt="sample_image" width="100%">
							</div>
						</div>
						<button type="button" class="btn btn-light btn-lg float-right" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo $this->lang->line('Close'); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>