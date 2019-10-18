<?php
  $postback_id_str = $bot_info['postback_id'];
  $postback_id_array = explode(",",$postback_id_str);
  $full_message_json = $bot_info['message'];
  $full_message_array = json_decode($full_message_json,true);
  // $full_message = $full_message_array['message'];
  $redirect_url = site_url('messenger_bot/bot_settings/').$page_info['id'].'/1';
  $image_upload_limit = 1; 
  if($this->config->item('messengerbot_image_upload_limit') != '')
  $image_upload_limit = $this->config->item('messengerbot_image_upload_limit'); 

  $video_upload_limit = 5; 
  if($this->config->item('messengerbot_video_upload_limit') != '')
  $video_upload_limit = $this->config->item('messengerbot_video_upload_limit');

  $audio_upload_limit = 3; 
  if($this->config->item('messengerbot_audio_upload_limit') != '')
  $audio_upload_limit = $this->config->item('messengerbot_audio_upload_limit');

  $file_upload_limit = 2; 
  if($this->config->item('messengerbot_file_upload_limit') != '')
  $file_upload_limit = $this->config->item('messengerbot_file_upload_limit');
  
  $THEMECOLORCODE = "#607D8B";

?>
<?php $this->load->view("include/upload_js"); ?>

<style type="text/css">
  .item_remove
  {
    margin-top: 12px; 
    margin-left: -20px;
    font-size: 20px !important;
    cursor: pointer !important;
    font-weight: 200 !important;
  }
  /*.remove_reply
  {
    margin:10px 10px 5px 0;
    font-size: 25px !important;
    cursor: pointer !important;
    font-weight: 200 !important;
  }*/
  .add_template,.ref_template{font-size: 10px;}
  .emojionearea.form-control{padding-top:12px !important;}
  .img_holder div:not(:first-child){display: none;position:fixed;bottom:187px;right:40px;}
  .img_holder div:first-child{position:fixed;bottom:187px;right:40px;}
  .lead_first_name,.lead_last_name{background: #EEE;border-radius: 0;}
  .input-group-addon{
    border-radius: 0;
    font-weight: bold;
    /* color: orange;   */
    /*border: 1px solid #607D8B !important;*/
    border: none;
    background: none;
  }
  
  input[type=radio].css-checkbox {
  position:absolute; z-index:-1000; left:-1000px; overflow: hidden; clip: rect(0 0 0 0); height:1px; width:1px; margin:-1px; padding:0; border:0;
  }

  input[type=radio].css-checkbox + label.css-label {
    padding-left:24px;
    height:19px; 
    display:inline-block;
    line-height:19px;
    background-repeat:no-repeat;
    background-position: 0 0;
    font-size:19px;
    vertical-align:middle;
    cursor:pointer;

  }

  input[type=radio].css-checkbox:checked + label.css-label {
    background-position: 0 -19px;
  }
  label.css-label {
    background-image:url(<?php echo base_url('assets/images/csscheckbox.png'); ?>);
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    color: <?php echo $THEMECOLORCODE; ?> !important;
    font-size: 15px !important;
  }
  .css-label-container{padding:10px;border:1px dashed <?php echo $THEMECOLORCODE; ?>;border-radius: 5px;}
  .img_holder img{
    border: 1px solid #ccc;
  }

  .emojionearea, .emojionearea.form-control
  {
    height: 120px !important;
  }

  .emojionearea.small-height
  {
    height: 120px !important;
  }
  .button_border
  {
    padding: 35px 15px 15px 15px; 
    margin: 5px 0px 0px; 
    border: 1px dashed #ccc;
  }

  .button-outline
  {
    background: #fff;
    border: .5px dashed #ccc;
  }
  .button-outline:hover
  {
    border: 1px dashed #6777EF !important;
    cursor: pointer;
  }
  
  .load_preview_modal
  {
    cursor: pointer;
  }

  <?php if($iframe == '1') : ?>
    .card-body
    {
      padding: 1px !important;
    }
  <?php endif; ?>
</style>


<?php if($iframe!='1') : ?>
<section class="section section_custom">
  <div class="section-header">
    <h1><i class='fa fa-edit'></i> <?php echo $this->lang->line("Edit Bot Settings");?> : <a target='_BLANK' href='https://facebook.com/<?php echo $page_info['page_id'];?>'><?php echo $page_info['page_name'];?></a></h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="<?php echo base_url('messenger_bot/bot_menu_section'); ?>"><?php echo $this->lang->line('Messenger Tools'); ?></a></div>
      <div class="breadcrumb-item"><?php echo $page_title; ?></div>
    </div>
  </div>
<?php endif; ?>

    <div class="card <?php if($iframe=='1') echo 'no_shadow';?>">
      <div class="card-body">

          <?php if($iframe!='1') : ?><div class="text-center waiting" id="loader"><i class="fas fa-spinner fa-spin blue text-center" style="font-size: 40px;"></i></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-12 col-md-9">
              <form action="#" method="post" id="messenger_bot_form" style="padding-left: 0;">
                <input type="hidden" name="id" id="id" value="<?php echo  $bot_info['id'];?>">
                <input type="hidden" name="page_id" id="page_id" value="<?php echo  $page_info['page_id'];?>">
                <input type="hidden" name="page_table_id" id="page_table_id" value="<?php echo  $page_info['id'];?>">
                <?php 
                  $type = 'reply';
                  if($default_template == 'getstart') $type = 'get-started';
                  else if($default_template == 'nomatch') $type = 'no match';
                ?>
                <input type="hidden" name="keyword_type" id="keyword_type" value="<?php echo $type; ?>">
                
                <div class="row"> 
                  <?php if($type == 'reply') : ?>
                  <div class="col-12 col-sm-6"> 
                    <div class="form-group">
                      <label><?php echo $this->lang->line("Bot Name"); ?></label>
                      <input type="text" name="bot_name" value="<?php if(set_value('bot_name')) echo set_value('bot_name');else {if(isset($bot_info['bot_name'])) echo $bot_info['bot_name'];}?>" id="bot_name" class="form-control">
                    </div>       
                  </div> 

                  <div class="col-12 col-sm-6">              
                    <div class="form-group">
                      <label><?php echo $this->lang->line("Please provide your keywords in comma separated"); ?></label>
                      <input class="form-control"  name="keywords_list" id="keywords_list" value="<?php if(set_value('keywords_list')) echo set_value('keywords_list');else {if(isset($bot_info['keywords'])) echo $bot_info['keywords'];}?>">
                    </div>        
                  </div> 
                  <?php else : ?> 
                  <div class="col-12" style="display: none;"> 
                    <div class="form-group">
                      <label><?php echo $this->lang->line("Bot Name"); ?></label>
                      <input type="hidden" name="bot_name" value="<?php if(set_value('bot_name')) echo set_value('bot_name');else {if(isset($bot_info['bot_name'])) echo $bot_info['bot_name'];}?>" id="bot_name" class="form-control">
                    </div>       
                  </div> 
                  <input type="hidden"  name="keywords_list" id="keywords_list" value="">
                  <?php endif; ?>
                </div>
               

               <div class="row" id="postback_div" style="display: none;"> 
                  <div class="col-12">              
                    <div class="form-group">
                      <label><?php echo $this->lang->line("Please select your postback id : ");?></label>   
                      <select class="form-control" id="keywordtype_postback_id_useless" name="keywordtype_postback_id_useless[]" disabled="disabled">
                      <?php
                          $hidden_input_value = '';
                          $total_postback_id_array = array();
                          foreach($postback_ids as $value)
                          {
                            if(!in_array($value['postback_id'], $current_postbacks))
                               $total_postback_id_array[] = strtoupper($value['postback_id']);

                            if($value["template_for"]=="unsubscribe" || $value["template_for"]=="resubscribe" || $value["template_for"]=="email-quick-reply" || $value["template_for"]=="phone-quick-reply" || $value["template_for"]=="location-quick-reply" || $value["is_template"] == "1") continue;

                            $array_key = $value['postback_id'];
                            $array_value = $value['postback_id']." (".$value['bot_name'].")";
                            if($value['use_status'] == '0')
                            {                              
                              echo "<option value='{$array_key}'>{$array_value}</option>";
                            } 
                            else
                            {
                              if(in_array($array_key, $postback_id_array))
                              {
                                $hidden_input_value = $array_key;
                                echo "<option value='{$array_key}' selected >{$array_value}</option>";                                
                              } 
                              
                            }                        
                          }
                      ?>                       
                      </select>
                      <input type='hidden' name='keywordtype_postback_id[]' id='keywordtype_postback_id' value='<?php echo $hidden_input_value; ?>'>
                    </div>        
                  </div>  
               </div>                    

              <?php 
              if(!isset($full_message_array[1]))
              {
                $full_message_array[1] = $full_message_array;
                $full_message_array[1]['message']['template_type'] = $bot_info['template_type'];
              }


              $active_reply_count = 0;
              for($k=1;$k<=3;$k++){ 

                $full_message[$k] = isset($full_message_array[$k]['message']) ? $full_message_array[$k]['message'] : array();

                if(isset($full_message[$k]["template_type"]))
                  $full_message[$k]["template_type"] = str_replace('_', ' ', $full_message[$k]["template_type"]);       

              ?>

              <div class="card card-primary" id="multiple_template_div_<?php echo $k; ?>" 
                <?php 
                  if(!isset($full_message[$k]["template_type"]))
                    echo "style='display : none;'"; 
                  else
                  {
                    $active_reply_count++;
                  }
                ?>
              >

                <div class="card-header">
                  <h4 class="full_width">
                    <?php echo $this->lang->line('Reply')." ".$k; ?>
                    <?php if($k != 1 && $k == count($full_message_array)) : ?>
                      <i class="fa fa-times-circle remove_reply float-right red" row_id="multiple_template_div_<?php echo $k; ?>" counter_variable="" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                    <?php else : ?>
                      <i class="fa fa-times-circle remove_reply float-right red" style="display: none;" row_id="multiple_template_div_<?php echo $k; ?>" counter_variable="" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                    <?php endif; ?>
                  </h4>
                </div>
                <div class="card-body">

                  <div <?php if($iframe == '1') echo 'style="padding: 0 15px 15px 15px !important;"' ?> >

                  <br/>
                  <div class="input-group">                            
                    <div class="input-group-prepend">
                      <div class="input-group-text" style="font-weight: bold;">
                        <?php echo $this->lang->line("Select Reply Type") ?>
                      </div>
                    </div>
                    <select class="form-control form-control-new" id="template_type_<?php echo $k; ?>" name="template_type_<?php echo $k; ?>">
                      <?php 
                       foreach ($templates as $key => $value)
                       {
                          if(isset($full_message[$k]["template_type"]) && $full_message[$k]["template_type"] == $value) $selected='selected';
                          else $selected='';
                          echo '<option value="'.$value.'" '.$selected.'>'.$this->lang->line($value).'</option>';
                       } 
                      ?>
                    </select>
                  </div> 
                  <br/>

                  <div class="row">
                    <div class="col-12 col-sm-6">
                      <div class="row">
                        <div class="col-6"><label for="" style="margin-top: 8px; color: #34395e; font-size: 14px;"><?php echo $this->lang->line('Typing on display :'); ?></label></div>
                        <div class="col-6">
                          <label class="custom-switch mt-2 float-left">
                            <input type="checkbox" name="typing_on_enable_<?php echo $k; ?>" id="typing_on_enable_<?php echo $k; ?>" class="custom-switch-input" <?php if(isset($full_message[$k]["typing_on_settings"]) && $full_message[$k]["typing_on_settings"] == 'on') echo 'checked'; ?> >
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description"><?php echo $this->lang->line('Enable'); ?></span>
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-sm-6">
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><?php echo $this->lang->line('Delay in reply'); ?></span>
                        </div>
                        <input type="number" min="0" value="<?php if(isset($full_message[$k]["delay_in_reply"])) echo $full_message[$k]["delay_in_reply"]; ?>" name="delay_in_reply_<?php echo $k; ?>" id="delay_in_reply_<?php echo $k; ?>" class="form-control">
                        <div class="input-group-append">
                          <span class="input-group-text"><?php echo $this->lang->line('Sec'); ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <br/>

                  <div class="row" id="text_div_<?php echo $k; ?>"> 
                    <div class="col-12">              
                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply message"); ?>
                          <a href="#" data-placement="bottom"  data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Spintax"); ?>" data-content="Spintax example : {Hello|Howdy|Hola} to you, {Mr.|Mrs.|Ms.} {{Jason|Malina|Sara}|Williams|Davis}"><i class='fa fa-info-circle'></i> </a>
                        </label>

                        <span class='float-right'> 
                          <a title="<?php echo $this->lang->line("You can include #LEAD_USER_LAST_NAME# variable inside your message. The variable will be replaced by real names when we will send it.") ?>" data-toggle="tooltip" data-placement="top" class='btn-sm lead_last_name button-outline'><i class='fa fa-user'></i> <?php echo $this->lang->line("last name") ?></a>
                        </span>
                        <span class='float-right'> 
                          <a title="<?php echo $this->lang->line("You can include #LEAD_USER_FIRST_NAME# variable inside your message. The variable will be replaced by real names when we will send it.") ?>" data-toggle="tooltip" data-placement="top" class='btn-sm lead_first_name button-outline'><i class='fa fa-user'></i> <?php echo $this->lang->line("first name") ?></a>
                        </span> 

						            <div class="clearfix"></div>
                        <textarea class="form-control"  name="text_reply_<?php echo $k; ?>" id="text_reply_<?php echo $k; ?>"><?php if(isset($full_message[$k]["template_type"]) && $full_message[$k]["template_type"] == 'text') echo $full_message[$k]['text'];?></textarea>
                      </div>        
                    </div>  
                  </div>

                  <div class="row" id="image_div_<?php echo $k; ?>" style="display: none;">             
                    <div class="col-12">              
                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply image"); ?></label>

                        <span class="badge badge-status blue load_preview_modal float-right" item_type="image" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type']== 'image') echo $full_message[$k]['attachment']['payload']['url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                        <input type="hidden" class="form-control"  name="image_reply_field_<?php echo $k; ?>" id="image_reply_field_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type']== 'image') echo $full_message[$k]['attachment']['payload']['url'];?>">
                        <div id="image_reply_<?php echo $k; ?>"><?php echo $this->lang->line("upload") ?></div>
                        <img id="image_reply_div_<?php echo $k; ?>" style="display: none;" height="200px;" width="400px;">
                      </div>       
                    </div>             
                  </div>

                  <div class="row" id="audio_div_<?php echo $k; ?>" style="display: none;">  
                    <div class="col-12">             
                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply audio"); ?></label>

                        <span class="badge badge-status blue load_preview_modal float-right" item_type="audio" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type']== 'audio') echo $full_message[$k]['attachment']['payload']['url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                        <input type="hidden" class="form-control"  name="audio_reply_field_<?php echo $k; ?>" id="audio_reply_field_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type']== 'audio') echo $full_message[$k]['attachment']['payload']['url'];?>">
                        <div id="audio_reply_<?php echo $k; ?>"><?php echo $this->lang->line("upload") ?></div>  
                        <audio controls id="audio_tag_<?php echo $k; ?>" style="display: none;">
                          <source src="" id="audio_reply_div_<?php echo $k; ?>" type="audio/mpeg">
                        Your browser does not support the video tag.
                        </audio>
                      </div>           
                    </div>
                  </div>

                  <div class="row" id="video_div_<?php echo $k; ?>" style="display: none;">  
                    <div class="col-12">             
                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply video"); ?></label>

                        <span class="badge badge-status blue load_preview_modal float-right" item_type="video" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'video') echo $full_message[$k]['attachment']['payload']['url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                        <input type="hidden" class="form-control"  name="video_reply_field_<?php echo $k; ?>" id="video_reply_field_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'video') echo $full_message[$k]['attachment']['payload']['url'];?>">
                        <div id="video_reply_<?php echo $k; ?>"><?php echo $this->lang->line("upload") ?></div> 
                        <video width="400" height="200" controls id="video_tag_<?php echo $k; ?>" style="display: none;">
                          <source src="" id="video_reply_div_<?php echo $k; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                        </video>
                      </div>           
                    </div>
                  </div>

                  <div class="row" id="file_div_<?php echo $k; ?>" style="display: none;">  
                    <div class="col-12">             
                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply file"); ?></label>

                        <span class="badge badge-status blue load_preview_modal float-right" item_type="file" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'file') echo $full_message[$k]['attachment']['payload']['url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                        <input type="hidden" class="form-control"  name="file_reply_field_<?php echo $k; ?>" id="file_reply_field_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'file') echo $full_message[$k]['attachment']['payload']['url'];?>">
                        <div id="file_reply_<?php echo $k; ?>"><?php echo $this->lang->line("upload") ?></div> 
                      </div>           
                    </div>
                  </div>


                  <div class="row" id="media_div_<?php echo $k; ?>" style="display: none; margin-bottom: 10px;">  
                    <div class="col-12"> 

                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your media URL"); ?>
                          <a href="#" class="media_template_modal" title="<?php echo $this->lang->line("How to get meida URL?"); ?>"><i class='fa fa-info-circle'></i> </a>
                        </label>

                        <div class="clearfix"></div>
                        <input class="form-control"  name="media_input_<?php echo $k; ?>" id="media_input_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]["template_type"]) && $full_message[$k]["template_type"] == 'media') echo $full_message[$k]['attachment']['payload']['elements'][0]['url']; ?>" />
                      </div> 

                      <?php $media_add_button_display = 0; for ($i=1; $i <=3 ; $i++) : ?>
                      <div class="row button_border" id="media_row_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1])) echo 'style="display: none;"'; else {$media_add_button_display++;} ?> >
                        <div class="col-12 col-sm-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line("button text"); ?></label>
                            <input type="text" class="form-control"  name="media_text_<?php echo $i; ?>_<?php echo $k; ?>" id="media_text_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['title'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['title']; ?>" >
                          </div>
                        </div>
                        <div class="col-12 col-sm-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line("button type"); ?></label>
                            <select class="form-control select2 media_type_class" id="media_type_<?php echo $i; ?>_<?php echo $k; ?>" name="media_type_<?php echo $i; ?>_<?php echo $k; ?>">
                              <option value=""><?php echo $this->lang->line('please select a type'); ?></option>
                              <option value="post_back" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback') echo 'selected'; ?> ><?php echo $this->lang->line("Post Back"); ?></option>
                              <option value="web_url" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'web_url') echo 'selected'; ?> ><?php echo $this->lang->line("Web Url"); ?></option>

                              <option value="web_url_compact" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio'] == 'compact') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Compact]"); ?></option>
                              <option value="web_url_tall" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio'] == 'tall') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Tall]"); ?></option>
                              <option value="web_url_full" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio'] == 'full') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Full]"); ?></option>
                              <option value="web_url_birthday" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false) echo 'selected'; ?> ><?php echo $this->lang->line("User's Birthday"); ?></option>

                              <option value="phone_number" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'phone_number') echo 'selected'; ?> ><?php echo $this->lang->line("call us"); ?></option>
                              
                              
                              <option value="post_back" id="unsubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("unsubscribe"); ?></option>
                              <option value="post_back" id="resubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("re-subscribe"); ?></option>
                              
                              <option value="post_back" id="human_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Human"); ?></option>
                              <option value="post_back" id="robot_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Robot"); ?></option>
                            </select>
                          </div>
                        </div>
                        <div class="col-10 col-sm-3">
                          
                          <div class="form-group" id="media_postid_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] != 'postback' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'style="display: none;"'; ?> >
                          <label><?php echo $this->lang->line("PostBack id"); ?></label>
                          <?php $pname="media_post_id_".$i."_".$k; ?>
                          <?php 
                          $pdefault=(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback') ? $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']:"";
                          if($pdefault == 'UNSUBSCRIBE_QUICK_BOXER')
                            $poption['UNSUBSCRIBE_QUICK_BOXER']=$this->lang->line('unsubscribe');
                          if($pdefault == 'RESUBSCRIBE_QUICK_BOXER')
                            $poption['RESUBSCRIBE_QUICK_BOXER']=$this->lang->line('re-subscribe');
                          if($pdefault == 'YES_START_CHAT_WITH_HUMAN')
                            $poption['YES_START_CHAT_WITH_HUMAN']=$this->lang->line('Chat with Human');
                          if($pdefault == 'YES_START_CHAT_WITH_BOT')
                            $poption['YES_START_CHAT_WITH_BOT']=$this->lang->line('Chat with Robot');
                          ?>
                          <?php echo form_dropdown($pname, $poption,$pdefault,'class="form-control push_postback" id="'.$pname.'"'); ?>
                          <a href="" class="add_template float-left"><i class="fa fa-plus-circle"></i>     <?php echo $this->lang->line("Add");?></a>
                          <a href="" class="ref_template float-right"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Refresh");?></a>

                          </div>

                          <div class="form-group" id="media_web_url_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']) || (isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false)) echo 'style="display: none;"'; ?>>
                            <label><?php echo $this->lang->line("Web Url"); ?></label>
                            <input type="text" class="form-control"  name="media_web_url_<?php echo $i; ?>_<?php echo $k; ?>" id="media_web_url_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']; ?>" >
                          </div>

                          <div class="form-group" id="media_call_us_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] != 'phone_number') echo 'style="display: none;"'; ?>>
                            <label><?php echo $this->lang->line("Phone Number"); ?></label>
                            <input type="text" class="form-control"  name="media_call_us_<?php echo $i; ?>_<?php echo $k; ?>" id="media_call_us_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'phone_number' ) echo $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']; ?>" >
                          </div>

                        </div>

                        <?php if($i != 1) : ?>
                          <div class="col-2 col-sm-1" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'])) if(count($full_message[$k]['attachment']['payload']['elements'][0]['buttons']) != $i) echo 'style="display: none;"'; ?>>
                            <br/>
                            <i class="fa fa-2x fa-times-circle red item_remove" template_type="not_quick_reply" row_id="media_row_<?php echo $i; ?>_<?php echo $k; ?>" first_column_id="media_text_<?php echo $i; ?>_<?php echo $k; ?>" second_column_id="media_type_<?php echo $i; ?>_<?php echo $k; ?>" third_postback="media_post_id_<?php echo $i; ?>_<?php echo $k; ?>" third_weburl="media_web_url_<?php echo $i; ?>_<?php echo $k; ?>" third_callus="media_call_us_<?php echo $i; ?>_<?php echo $k; ?>" counter_variable="media_counter_<?php echo $k; ?>" add_more_button_id="media_add_button_<?php echo $k; ?>" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                          </div>
                        <?php endif; ?>

                      </div>
                      <?php endfor; ?>

                      <div class="row clearfix">
                        <div class="col-12 text-center"><button class="btn btn-outline-primary float-right no_radius btn-xs" <?php if($media_add_button_display==3) echo 'style="display : none;"'; ?> id="media_add_button_<?php echo $k; ?>"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more button");?></button></div>
                      </div>

                    </div> 
                  </div>


                  <div class="row" id="quick_reply_div_<?php echo $k; ?>" style="display: none; margin-bottom: 10px;">  
                    <div class="col-12">  

                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply message"); ?>
                          <a href="#" data-placement="bottom"  data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Spintax"); ?>" data-content="Spintax example : {Hello|Howdy|Hola} to you, {Mr.|Mrs.|Ms.} {{Jason|Malina|Sara}|Williams|Davis}"><i class='fa fa-info-circle'></i> </a>
                        </label>

                        <span class='float-right'> 
                          <a title="<?php echo $this->lang->line("You can include #LEAD_USER_LAST_NAME# variable inside your message. The variable will be replaced by real names when we will send it.") ?>" data-toggle="tooltip" data-placement="top" class='btn-sm lead_last_name button-outline'><i class='fa fa-user'></i> <?php echo $this->lang->line("last name") ?></a>
                        </span>
                        <span class='float-right'> 
                          <a title="<?php echo $this->lang->line("You can include #LEAD_USER_FIRST_NAME# variable inside your message. The variable will be replaced by real names when we will send it.") ?>" data-toggle="tooltip" data-placement="top" class='btn-sm lead_first_name button-outline'><i class='fa fa-user'></i> <?php echo $this->lang->line("first name") ?></a>
                        </span> 

						            <div class="clearfix"></div>
                        <textarea class="form-control" name="quick_reply_text_<?php echo $k; ?>" id="quick_reply_text_<?php echo $k; ?>"><?php if(isset($full_message[$k]["template_type"]) && $full_message[$k]["template_type"] == 'quick reply') echo $full_message[$k]['text'];?></textarea>
                      </div> 

                      <?php $quickreply_add_button_display = 0; for ($i=1; $i <=11 ; $i++) : ?>
                      <div class="row button_border" id="quick_reply_row_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['quick_replies'][$i-1])) echo 'style="display: none;"'; else {$quickreply_add_button_display++;} ?> >
                        <div class="col-12 col-sm-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line("button text"); ?></label>
                            <input type="text" class="form-control"  name="quick_reply_button_text_<?php echo $i; ?>_<?php echo $k; ?>" id="quick_reply_button_text_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['quick_replies'][$i-1]['title'])) echo $full_message[$k]['quick_replies'][$i-1]['title']; ?>" <?php if(isset($full_message[$k]['quick_replies'][$i-1]['content_type']) && ($full_message[$k]['quick_replies'][$i-1]['content_type'] == 'user_phone_number' || $full_message[$k]['quick_replies'][$i-1]['content_type'] == 'user_email')) echo 'readonly'; ?>>
                          </div>
                        </div>

                        <div class="col-12 col-sm-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line("button type"); ?></label>
                            <select class="form-control select2 quick_reply_button_type_class" id="quick_reply_button_type_<?php echo $i; ?>_<?php echo $k; ?>" name="quick_reply_button_type_<?php echo $i; ?>_<?php echo $k; ?>">
                              <option value=""><?php echo $this->lang->line('please select a type'); ?></option>
                              <option value="post_back" <?php if(isset($full_message[$k]['quick_replies'][$i-1]['content_type']) && $full_message[$k]['quick_replies'][$i-1]['content_type'] == 'text') echo 'selected'; ?> ><?php echo $this->lang->line("Post Back"); ?></option>
                              <option value="phone_number" <?php if(isset($full_message[$k]['quick_replies'][$i-1]['content_type']) && $full_message[$k]['quick_replies'][$i-1]['content_type'] == 'user_phone_number') echo 'selected'; ?> ><?php echo $this->lang->line("User Phone Number"); ?></option>
                              <option value="user_email" <?php if(isset($full_message[$k]['quick_replies'][$i-1]['content_type']) && $full_message[$k]['quick_replies'][$i-1]['content_type'] == 'user_email') echo 'selected'; ?> ><?php echo $this->lang->line("User E-mail Address"); ?></option>
                              <!-- <option value="location" <?php if(isset($full_message[$k]['quick_replies'][$i-1]['content_type']) && $full_message[$k]['quick_replies'][$i-1]['content_type'] == 'location') echo 'selected'; ?> ><?php echo $this->lang->line("User's Location"); ?></option> -->
                            </select>
                          </div>
                        </div>
                        <div class="col-10 col-sm-3">
                          <div class="form-group" id="quick_reply_postid_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['quick_replies'][$i-1]['content_type']) || $full_message[$k]['quick_replies'][$i-1]['content_type'] != 'text') echo 'style="display: none;"'; ?>>
                            <label><?php echo $this->lang->line("PostBack id"); ?></label>
                            <?php $pname="quick_reply_post_id_".$i."_".$k; ?>
                            <?php $pdefault=(isset($full_message[$k]['quick_replies'][$i-1]['payload'])) ? $full_message[$k]['quick_replies'][$i-1]['payload']:"";?>
                            <?php echo form_dropdown($pname, $poption,$pdefault,'class="form-control push_postback" id="'.$pname.'"'); ?>
                            <a href="" class="add_template float-left"><i class="fa fa-plus-circle"></i>     <?php echo $this->lang->line("Add");?></a>
                            <a href="" class="ref_template float-right"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Refresh");?></a>
                          </div>
                        </div>

                        <?php if($i != 1) : ?>
                          <div class="col-2 col-sm-1" <?php if(isset($full_message[$k]['quick_replies'])) if(count($full_message[$k]['quick_replies']) != $i) echo 'style="display: none;"'; ?> >
                            <br/>
                            <i class="fa fa-2x fa-times-circle red item_remove" template_type="quick_reply" row_id="quick_reply_row_<?php echo $i; ?>_<?php echo $k; ?>" first_column_id="quick_reply_button_text_<?php echo $i; ?>_<?php echo $k; ?>" second_column_id="quick_reply_button_type_<?php echo $i; ?>_<?php echo $k; ?>" third_postback="quick_reply_post_id_<?php echo $i; ?>_<?php echo $k; ?>" third_weburl="" third_callus="" counter_variable="quick_reply_button_counter_<?php echo $k; ?>" add_more_button_id="quick_reply_add_button_<?php echo $k; ?>" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                          </div>
                        <?php endif; ?>


                      </div>
                      <?php endfor; ?>

                      <div class="row clearfix">
                        <div class="col-12 text-center"><button class="btn btn-outline-primary float-right no_radius btn-xs" <?php if($quickreply_add_button_display==11) echo 'style="display : none;"'; ?> id="quick_reply_add_button_<?php echo $k; ?>"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more button");?></button></div>
                      </div>

                    </div> 
                  </div>


                  <div class="row" id="text_with_buttons_div_<?php echo $k; ?>" style="display: none; margin-bottom: 10px;">  
                    <div class="col-12"> 

                      <div class="form-group">
                        <label><?php echo $this->lang->line("Please provide your reply message"); ?>
                          <a href="#" data-placement="bottom"  data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Spintax"); ?>" data-content="Spintax example : {Hello|Howdy|Hola} to you, {Mr.|Mrs.|Ms.} {{Jason|Malina|Sara}|Williams|Davis}"><i class='fa fa-info-circle'></i> </a>
                        </label>

                        <span class='float-right'> 
                          <a title="<?php echo $this->lang->line("You can include #LEAD_USER_LAST_NAME# variable inside your message. The variable will be replaced by real names when we will send it.") ?>" data-toggle="tooltip" data-placement="top" class='btn-sm lead_last_name button-outline'><i class='fa fa-user'></i> <?php echo $this->lang->line("last name") ?></a>
                        </span>
                        <span class='float-right'> 
                          <a title="<?php echo $this->lang->line("You can include #LEAD_USER_FIRST_NAME# variable inside your message. The variable will be replaced by real names when we will send it.") ?>" data-toggle="tooltip" data-placement="top" class='btn-sm lead_first_name button-outline'><i class='fa fa-user'></i> <?php echo $this->lang->line("first name") ?></a>
                        </span> 

						            <div class="clearfix"></div>
                        <textarea class="form-control"  name="text_with_buttons_input_<?php echo $k; ?>" id="text_with_buttons_input_<?php echo $k; ?>"><?php if(isset($full_message[$k]["template_type"]) && $full_message[$k]["template_type"] == 'text with buttons') echo $full_message[$k]['attachment']['payload']['text']; ?></textarea>
                      </div> 

                      <?php $textwithbutton_add_button_display = 0; for ($i=1; $i <=3 ; $i++) : ?>
                      <div class="row button_border" id="text_with_buttons_row_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][$i-1])) echo 'style="display: none;"'; else {$textwithbutton_add_button_display++;} ?> >
                        <div class="col-12 col-sm-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line("button text"); ?></label>
                            <input type="text" class="form-control"  name="text_with_buttons_text_<?php echo $i; ?>_<?php echo $k; ?>" id="text_with_buttons_text_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['title'])) echo $full_message[$k]['attachment']['payload']['buttons'][$i-1]['title']; ?>" >
                          </div>
                        </div>
                        <div class="col-12 col-sm-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line("button type"); ?></label>
                            <select class="form-control select2 text_with_button_type_class" id="text_with_button_type_<?php echo $i; ?>_<?php echo $k; ?>" name="text_with_button_type_<?php echo $i; ?>_<?php echo $k; ?>">
                              <option value=""><?php echo $this->lang->line('please select a type'); ?></option>
                              <option value="post_back" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'postback') echo 'selected'; ?> ><?php echo $this->lang->line("Post Back"); ?></option>
                              <option value="web_url" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'web_url') echo 'selected'; ?> ><?php echo $this->lang->line("Web Url"); ?></option>

                              <option value="web_url_compact" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['webview_height_ratio'] == 'compact') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Compact]"); ?></option>
                              <option value="web_url_tall" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['webview_height_ratio'] == 'tall') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Tall]"); ?></option>
                              <option value="web_url_full" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['webview_height_ratio'] == 'full') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Full]"); ?></option>
                              <option value="web_url_birthday" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false) echo 'selected'; ?> ><?php echo $this->lang->line("User's Birthday"); ?></option>


                              <option value="phone_number" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'phone_number') echo 'selected'; ?> ><?php echo $this->lang->line("call us"); ?></option>

                              
                              <option value="post_back" id="unsubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("unsubscribe"); ?></option>
                              <option value="post_back" id="resubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("re-subscribe"); ?></option>
                              
                              <option value="post_back" id="human_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Human"); ?></option>
                              <option value="post_back" id="robot_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Robot"); ?></option>
                            </select>
                          </div>
                        </div>
                        <div class="col-10 col-sm-3">
                          <div class="form-group" id="text_with_button_postid_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] != 'postback' || $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN' || $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'style="display: none;"'; ?> >
                            <label><?php echo $this->lang->line("PostBack id"); ?></label>
                            <?php $pname="text_with_button_post_id_".$i."_".$k; ?>
                            <?php 
                            $pdefault=(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'postback') ? $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']:"";
                            if($pdefault == 'UNSUBSCRIBE_QUICK_BOXER')
                              $poption['UNSUBSCRIBE_QUICK_BOXER']=$this->lang->line('unsubscribe');
                            if($pdefault == 'RESUBSCRIBE_QUICK_BOXER')
                              $poption['RESUBSCRIBE_QUICK_BOXER']=$this->lang->line('re-subscribe');
                            if($pdefault == 'YES_START_CHAT_WITH_HUMAN')
                              $poption['YES_START_CHAT_WITH_HUMAN']=$this->lang->line('Chat with Human');
                            if($pdefault == 'YES_START_CHAT_WITH_BOT')
                              $poption['YES_START_CHAT_WITH_BOT']=$this->lang->line('Chat with Robot');
                            ?>
                            <?php echo form_dropdown($pname, $poption,$pdefault,'class="form-control push_postback" id="'.$pname.'"'); ?>
                            <a href="" class="add_template float-left"><i class="fa fa-plus-circle"></i>     <?php echo $this->lang->line("Add");?></a>
                            <a href="" class="ref_template float-right"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Refresh");?></a>
                            
                          </div>
                          <div class="form-group" id="text_with_button_web_url_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['url']) || (isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false)) echo 'style="display: none;"'; ?>>
                            <label><?php echo $this->lang->line("Web Url"); ?></label>
                            <input type="text" class="form-control"  name="text_with_button_web_url_<?php echo $i; ?>_<?php echo $k; ?>" id="text_with_button_web_url_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['url'])) echo $full_message[$k]['attachment']['payload']['buttons'][$i-1]['url']; ?>" >
                          </div>

                          <div class="form-group" id="text_with_button_call_us_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] != 'phone_number') echo 'style="display: none;"'; ?>>
                            <label><?php echo $this->lang->line("Phone Number"); ?></label>
                            <input type="text" class="form-control"  name="text_with_button_call_us_<?php echo $i; ?>_<?php echo $k; ?>" id="text_with_button_call_us_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][$i-1]['type'] == 'phone_number' ) echo $full_message[$k]['attachment']['payload']['buttons'][$i-1]['payload']; ?>" >
                          </div>

                        </div>

                        <?php if($i != 1) : ?>
                          <div class="col-2 col-sm-1" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'])) if(count($full_message[$k]['attachment']['payload']['buttons']) != $i) echo 'style="display: none;"'; ?>>
                            <br/>
                            <i class="fa fa-2x fa-times-circle red item_remove" template_type="not_quick_reply" row_id="text_with_buttons_row_<?php echo $i; ?>_<?php echo $k; ?>" first_column_id="text_with_buttons_text_<?php echo $i; ?>_<?php echo $k; ?>" second_column_id="text_with_button_type_<?php echo $i; ?>_<?php echo $k; ?>" third_postback="text_with_button_post_id_<?php echo $i; ?>_<?php echo $k; ?>" third_weburl="text_with_button_web_url_<?php echo $i; ?>_<?php echo $k; ?>" third_callus="text_with_button_call_us_<?php echo $i; ?>_<?php echo $k; ?>" counter_variable="text_with_button_counter_<?php echo $k; ?>" add_more_button_id="text_with_button_add_button_<?php echo $k; ?>" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                          </div>
                        <?php endif; ?>


                      </div>
                      <?php endfor; ?>

                      <div class="row clearfix">
                        <div class="col-12 text-center"><button class="btn btn-outline-primary float-right no_radius btn-xs" <?php if($textwithbutton_add_button_display==3) echo 'style="display : none;"'; ?> id="text_with_button_add_button_<?php echo $k; ?>"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more button");?></button></div>
                      </div>

                    </div> 
                  </div>

                  <div class="row" id="generic_template_div_<?php echo $k; ?>" style="display: none; margin-bottom: 10px;">   
                    <div class="col-12"> 

                      <div class="card card-secondary">
                        <div class="card-header">
                          <h4><?php echo $this->lang->line('Generic Template'); ?></h4>
                        </div>
                        <div class="card-body">
                          <div style="padding: 10px 20px;">

                            <div class="row">
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("Please provide your reply image"); ?> <span style='color:orange !important;'>(<?php echo $this->lang->line("optional"); ?>)</span></label>

                                  <span class="badge badge-status blue load_preview_modal float-right" item_type="image" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'generic template' && isset($full_message[$k]['attachment']['payload']['elements'][0]['image_url'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['image_url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                                  <input type="hidden" class="form-control"  name="generic_template_image_<?php echo $k; ?>" id="generic_template_image_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'generic template' && isset($full_message[$k]['attachment']['payload']['elements'][0]['image_url'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['image_url'];?>" />
                                  <div id="generic_image_<?php echo $k; ?>"><?php echo $this->lang->line('upload'); ?></div>
                                </div>                         
                              </div>
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("image click destination link"); ?> <span style='color:orange !important;'>(<?php echo $this->lang->line("optional"); ?>)</span></label>
                                  <input type="text" class="form-control"  name="generic_template_image_destination_link_<?php echo $k; ?>" id="generic_template_image_destination_link_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'generic template' && isset($full_message[$k]['attachment']['payload']['elements'][0]['default_action']['url'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['default_action']['url'];?>"/>
                                </div> 
                              </div>                      
                            </div>

                            <div class="row">
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("title"); ?></label>
                                  <input type="text" class="form-control"  name="generic_template_title_<?php echo $k; ?>" id="generic_template_title_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'generic template' && isset($full_message[$k]['attachment']['payload']['elements'][0]['title'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['title'];?>"/>
                                </div>
                              </div>  
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("sub-title"); ?></label>
                                  <input type="text" class="form-control"  name="generic_template_subtitle_<?php echo $k; ?>" id="generic_template_subtitle_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'generic template' && isset($full_message[$k]['attachment']['payload']['elements'][0]['subtitle'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['subtitle'];?>" />
                                </div>
                              </div>  
                            </div>

                            <span class="float-right"><span style='color:orange !important;'>(<?php echo $this->lang->line("optional"); ?>)</span></span><div class="clearfix"></div>
                            <?php $generic_add_button_display = 0; for ($i=1; $i <=3 ; $i++) : ?>
                            <div class="row button_border" id="generic_template_row_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1])) echo 'style="display: none;"'; else {$generic_add_button_display++;} ?> >
                              <div class="col-12 col-sm-4">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("button text"); ?></label>
                                  <input type="text" class="form-control"  name="generic_template_button_text_<?php echo $i; ?>_<?php echo $k; ?>" id="generic_template_button_text_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['title'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['title']; ?>">
                                </div>
                              </div>
                              <div class="col-12 col-sm-4">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("button type"); ?></label>
                                  <select class="form-control select2 generic_template_button_type_class" id="generic_template_button_type_<?php echo $i; ?>_<?php echo $k; ?>" name="generic_template_button_type_<?php echo $i; ?>_<?php echo $k; ?>">
                                    <option value=""><?php echo $this->lang->line('please select a type'); ?></option>
                                    <option value="post_back" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback') echo 'selected'; ?> ><?php echo $this->lang->line("Post Back"); ?></option>
                                    <option value="web_url" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'web_url') echo 'selected'; ?> ><?php echo $this->lang->line("Web Url"); ?></option>

                                    <option value="web_url_compact" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio'] == 'compact') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Compact]"); ?></option>
                                    <option value="web_url_tall" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio'] == 'tall') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Tall]"); ?></option>
                                    <option value="web_url_full" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['webview_height_ratio'] == 'full') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Full]"); ?></option>
                                    <option value="web_url_birthday" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false) echo 'selected'; ?> ><?php echo $this->lang->line("User's Birthday"); ?></option>

                                    <option value="phone_number" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'phone_number') echo 'selected'; ?> ><?php echo $this->lang->line("call us"); ?></option>

                                    
                                    <option value="post_back" id="unsubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("unsubscribe"); ?></option>
                                    <option value="post_back" id="resubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("re-subscribe"); ?></option>
                                    
                                    <option value="post_back" id="human_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Human"); ?></option>
                                    <option value="post_back" id="robot_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Robot"); ?></option>
                                  </select>
                                </div>
                              </div>
                              <div class="col-10 col-sm-3">
                                <div class="form-group" id="generic_template_button_postid_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] != 'postback' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN' || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'style="display: none;"'; ?>>
                                  <label><?php echo $this->lang->line("PostBack id"); ?></label>
                                  <?php $pname="generic_template_button_post_id_".$i."_".$k; ?>
                                  <?php 
                                  $pdefault=(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'postback') ? $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload'] : "";
                                  if($pdefault == 'UNSUBSCRIBE_QUICK_BOXER')
                                    $poption['UNSUBSCRIBE_QUICK_BOXER']=$this->lang->line('unsubscribe');
                                  if($pdefault == 'RESUBSCRIBE_QUICK_BOXER')
                                    $poption['RESUBSCRIBE_QUICK_BOXER']=$this->lang->line('re-subscribe');
                                  if($pdefault == 'YES_START_CHAT_WITH_HUMAN')
                                    $poption['YES_START_CHAT_WITH_HUMAN']=$this->lang->line('Chat with Human');
                                  if($pdefault == 'YES_START_CHAT_WITH_BOT')
                                    $poption['YES_START_CHAT_WITH_BOT']=$this->lang->line('Chat with Robot');
                                  ?>
                                  <?php echo form_dropdown($pname, $poption,$pdefault,'class="form-control push_postback" id="'.$pname.'"'); ?>                        
                                  <a href="" class="add_template float-left"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("Add");?></a>
                                  <a href="" class="ref_template float-right"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Refresh");?></a>
                                </div>
                                <div class="form-group" id="generic_template_button_web_url_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']) || (isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false)) echo 'style="display: none;"'; ?>>
                                  <label><?php echo $this->lang->line("Web Url"); ?></label>
                                  <input type="text" class="form-control"  name="generic_template_button_web_url_<?php echo $i; ?>_<?php echo $k; ?>" id="generic_template_button_web_url_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url'])) echo $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['url']; ?>" >
                                </div>
                                <div class="form-group" id="generic_template_button_call_us_div_<?php echo $i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] != 'phone_number') echo 'style="display: none;"'; ?>>
                                  <label><?php echo $this->lang->line("Phone Number"); ?></label>
                                  <input type="text" class="form-control"  name="generic_template_button_call_us_<?php echo $i; ?>_<?php echo $k; ?>" id="generic_template_button_call_us_<?php echo $i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['type'] == 'phone_number') echo $full_message[$k]['attachment']['payload']['elements'][0]['buttons'][$i-1]['payload']; ?>" >
                                </div>
                              </div>

                              <?php if($i != 1) : ?>
                                <div class="col-2 col-sm-1" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][0]['buttons'])) if(count($full_message[$k]['attachment']['payload']['elements'][0]['buttons']) != $i) echo 'style="display: none;"'; ?>>
                                  <br/>
                                  <i class="fa fa-2x fa-times-circle red item_remove" template_type="not_quick_reply" row_id="generic_template_row_<?php echo $i; ?>_<?php echo $k; ?>" first_column_id="generic_template_button_text_<?php echo $i; ?>_<?php echo $k; ?>" second_column_id="generic_template_button_type_<?php echo $i; ?>_<?php echo $k; ?>" third_postback="generic_template_button_post_id_<?php echo $i; ?>_<?php echo $k; ?>" third_weburl="generic_template_button_web_url_<?php echo $i; ?>_<?php echo $k; ?>" third_callus="generic_template_button_call_us_<?php echo $i; ?>_<?php echo $k; ?>" counter_variable="generic_with_button_counter_<?php echo $k; ?>" add_more_button_id="generic_template_add_button_<?php echo $k; ?>" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                                </div>
                              <?php endif; ?>


                            </div>
                            <?php endfor; ?>

                            <div class="row clearfix">
                              <div class="col-12 text-center"><button class="btn btn-outline-primary float-right no_radius btn-xs" <?php if($generic_add_button_display==3) echo 'style="display : none;"'; ?> id="generic_template_add_button_<?php echo $k; ?>"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more button");?></button></div>
                            </div>

                          </div>
                        </div> <!-- end of card body -->
                      </div>
                    </div>
                  </div>

                  <div class="row" id="carousel_div_<?php echo $k; ?>" style="display: none; margin-bottom: 10px;">  
                    <?php for ($j=1; $j <=10 ; $j++) : ?>
                    <div class="col-12" id="carousel_div_<?php echo $j; ?>_<?php echo $k; ?>" style="<?php if(!isset($full_message[$k]['attachment']['payload']['elements'][$j-1])) echo 'display: none;'; ?>"> 
                      <div class="card card-secondary">
                        <div class="card-header">
                          <h4><?php echo $this->lang->line('Carousel Template').' '.$j; ?></h4>
                        </div>
                        <div class="card-body">
                          <div style="padding: 10px 20px;">

                            <div class="row">
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("Please provide your reply image"); ?> <span style='color:orange !important;'>(<?php echo $this->lang->line("optional"); ?>)</span></label>

                                  <span class="badge badge-status blue load_preview_modal float-right" item_type="image" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'carousel' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                                  <input type="hidden" class="form-control"  name="carousel_image_<?php echo $j; ?>_<?php echo $k; ?>" id="carousel_image_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'carousel' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'];?>"/>
                                  <div id="generic_imageupload_<?php echo $j; ?>_<?php echo $k; ?>"><?php echo $this->lang->line('upload'); ?></div>
                                </div>                         
                              </div>
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("image click destination link"); ?> <span style='color:orange !important;'>(<?php echo $this->lang->line("optional"); ?>)</span></label>
                                  <input type="text" class="form-control"  name="carousel_image_destination_link_<?php echo $j; ?>_<?php echo $k; ?>" id="carousel_image_destination_link_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'carousel' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['default_action']['url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['default_action']['url'];?>"/>
                                </div> 
                              </div>                      
                            </div>

                            <div class="row">
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("title"); ?></label>
                                  <input type="text" class="form-control"  name="carousel_title_<?php echo $j; ?>_<?php echo $k; ?>" id="carousel_title_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'carousel' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['title'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['title'];?>" />
                                </div>
                              </div>  
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("sub-title"); ?></label>
                                  <input type="text" class="form-control"  name="carousel_subtitle_<?php echo $j; ?>_<?php echo $k; ?>" id="carousel_subtitle_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'carousel' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['subtitle'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['subtitle'];?>" />
                                </div>
                              </div>  
                            </div>

                            <span class="float-right"><span style='color:orange !important;'>(<?php echo $this->lang->line("optional"); ?>)</span></span><div class="clearfix"></div>
                            <?php $carousel_add_button_display = 0; for ($i=1; $i <=3 ; $i++) : ?>
                            <div class="row button_border" id="carousel_row_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1])) echo 'style="display: none;"'; else {$carousel_add_button_display++;} ?>>
                              <div class="col-12 col-sm-4">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("button text"); ?></label>
                                  <input type="text" class="form-control"  name="carousel_button_text_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" id="carousel_button_text_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['title'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['title']; ?>" >
                                </div>
                              </div>
                              <div class="col-12 col-sm-4">
                                <div class="form-group">
                                  <label><?php echo $this->lang->line("button type"); ?></label>
                                  <select class="form-control select2 carousel_button_type_class" id="carousel_button_type_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" name="carousel_button_type_<?php echo $j."_".$i; ?>_<?php echo $k; ?>">
                                    <option value=""><?php echo $this->lang->line('please select a type'); ?></option>
                                    <option value="post_back" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'postback') echo 'selected'; ?> ><?php echo $this->lang->line("Post Back"); ?></option>
                                    <option value="web_url" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'web_url') echo 'selected'; ?> ><?php echo $this->lang->line("Web Url"); ?></option>

                                    <option value="web_url_compact" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['webview_height_ratio'] == 'compact') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Compact]"); ?></option>
                                    <option value="web_url_tall" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['webview_height_ratio'] == 'tall') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Tall]"); ?></option>
                                    <option value="web_url_full" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['webview_height_ratio'] == 'full') echo 'selected'; ?>><?php echo $this->lang->line("WebView [Full]"); ?></option>
                                    <option value="web_url_birthday" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false) echo 'selected'; ?>><?php echo $this->lang->line("User's Birthday"); ?></option>

                                    <option value="phone_number" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'phone_number') echo 'selected'; ?> ><?php echo $this->lang->line("call us"); ?></option>

                                    
                                    <option value="post_back" id="unsubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("unsubscribe"); ?></option>
                                    <option value="post_back" id="resubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("re-subscribe"); ?></option>
                                    
                                      <option value="post_back" id="human_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Human"); ?></option>
                                    <option value="post_back" id="robot_postback" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Robot"); ?></option>
                                  </select>
                                </div>
                              </div>
                              <div class="col-10 col-sm-3">
                                <div class="form-group" id="carousel_button_postid_div_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] != 'postback' || $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'RESUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_HUMAN'|| $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'style="display: none;"'; ?> >
                                  <label><?php echo $this->lang->line("PostBack id"); ?></label>
                                  <?php $pname="carousel_button_post_id_".$j."_".$i."_".$k; ?>
                                  <?php 
                                  $pdefault=(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'postback') ? $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']:"";
                                  if($pdefault == 'UNSUBSCRIBE_QUICK_BOXER')
                                    $poption['UNSUBSCRIBE_QUICK_BOXER']=$this->lang->line('unsubscribe');
                                  if($pdefault == 'RESUBSCRIBE_QUICK_BOXER')
                                    $poption['RESUBSCRIBE_QUICK_BOXER']=$this->lang->line('re-subscribe');
                                  if($pdefault == 'YES_START_CHAT_WITH_HUMAN')
                                    $poption['YES_START_CHAT_WITH_HUMAN']=$this->lang->line('Chat with Human');
                                  if($pdefault == 'YES_START_CHAT_WITH_BOT')
                                    $poption['YES_START_CHAT_WITH_BOT']=$this->lang->line('Chat with Robot');
                                  ?>
                                  <?php echo form_dropdown($pname, $poption,$pdefault,'class="form-control push_postback" id="'.$pname.'"'); ?>                        
                                  <a href="" class="add_template float-left"><i class="fa fa-plus-circle"></i>     <?php echo $this->lang->line("Add");?></a>
                                  <a href="" class="ref_template float-right"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Refresh");?></a>
                                </div>
                                <div class="form-group" id="carousel_button_web_url_div_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url']) || (isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url']) && strpos($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url'],'webview_builder/get_birthdate') !== false)) echo 'style="display: none;"'; ?>>
                                  <label><?php echo $this->lang->line("Web Url"); ?></label>
                                  <input type="text" class="form-control"  name="carousel_button_web_url_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" id="carousel_button_web_url_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['url']; ?>" >
                                </div>
                                <div class="form-group" id="carousel_button_call_us_div_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) || $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] != 'phone_number') echo 'style="display: none;"'; ?> >
                                  <label><?php echo $this->lang->line("Phone Number"); ?></label>
                                  <input type="text" class="form-control"  name="carousel_button_call_us_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" id="carousel_button_call_us_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']) && $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['type'] == 'phone_number') echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'][$i-1]['payload']; ?>">
                                </div>
                              </div>

                              <?php if($i != 1) : ?>
                                <div class="col-2 col-sm-1" <?php if(isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons'])) if(count($full_message[$k]['attachment']['payload']['elements'][$j-1]['buttons']) != $i) echo 'style="display: none;"'; ?> >
                                  <br/>
                                  <i class="fa fa-2x fa-times-circle red item_remove" template_type="not_quick_reply" row_id="carousel_row_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" first_column_id="carousel_button_text_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" second_column_id="carousel_button_type_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" third_postback="carousel_button_post_id_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" third_weburl="carousel_button_web_url_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" third_callus="carousel_button_call_us_<?php echo $j."_".$i; ?>_<?php echo $k; ?>" counter_variable="carousel_add_button_counter_<?php echo $j; ?>_<?php echo $k; ?>" add_more_button_id="carousel_add_button_<?php echo $j; ?>_<?php echo $k; ?>" title="<?php echo $this->lang->line('Remove this item'); ?>"></i>
                                </div>
                              <?php endif; ?>

                            </div>
                            <?php endfor; ?>

                            <div class="row clearfix" style="padding-bottom: 10px;">
                              <div class="col-12 text-center"><button class="btn btn-outline-primary float-right no_radius btn-xs" <?php if($carousel_add_button_display==3) echo 'style="display : none;"'; ?> id="carousel_add_button_<?php echo $j; ?>_<?php echo $k; ?>"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more button");?></button></div>
                            </div>
                          </div>

                        </div> <!-- end of card body -->
                      </div>
                    </div>
                    <?php endfor; ?>

                    <div class="col-12 clearfix">
                      <button id="carousel_template_add_button_<?php echo $k; ?>" class="btn btn-sm btn-outline-primary float-right no_radius"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more template");?></button>
                    </div>

                  </div>


                  <div class="row" id="list_div_<?php echo $k; ?>" style="display: none; padding-top: 20px;">  
                     <div class="col-12">
                        <div class="row" id="list_with_buttons_row">
                           <div class="col-4">
                              <div class="form-group">
                                 <label><?php echo $this->lang->line("bottom button text"); ?></label>
                                 <input type="text" class="form-control"  name="list_with_buttons_text_<?php echo $k; ?>" id="list_with_buttons_text_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['title'])) echo $full_message[$k]['attachment']['payload']['buttons'][0]['title']; ?>">
                              </div>
                           </div>
                           <div class="col-4">
                              <div class="form-group">
                                 <label><?php echo $this->lang->line("bottom button type"); ?></label>
                                 <select class="form-control select2 list_with_button_type_class" id="list_with_button_type_<?php echo $k; ?>" name="list_with_button_type_<?php echo $k; ?>">
                                    <option value=""><?php echo $this->lang->line('please select a type'); ?></option>
                                    <option value="post_back" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'postback') echo 'selected'; ?> ><?php echo $this->lang->line("Post Back"); ?></option>
                                    <option value="web_url" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'web_url') echo 'selected'; ?> ><?php echo $this->lang->line("Web Url"); ?></option>

                                    <option value="web_url_compact" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['buttons'][0]['webview_height_ratio'] == 'compact') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Compact]"); ?></option>
                                    <option value="web_url_tall" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['buttons'][0]['webview_height_ratio'] == 'tall') echo 'selected'; ?> ><?php echo $this->lang->line("WebView [Tall]"); ?></option>
                                    <option value="web_url_full" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['webview_height_ratio']) && $full_message[$k]['attachment']['payload']['buttons'][0]['webview_height_ratio'] == 'full') echo 'selected'; ?>><?php echo $this->lang->line("WebView [Full]"); ?></option>
                                    <option value="web_url_birthday" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['url']) && strpos($full_message[$k]['attachment']['payload']['buttons'][0]['url'],'webview_builder/get_birthdate') !== false) echo 'selected'; ?> ><?php echo $this->lang->line("User's Birthday"); ?></option>

                                    <option value="phone_number" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'phone_number') echo 'selected'; ?> ><?php echo $this->lang->line("call us"); ?></option>
                                    
                                       <option value="post_back" id="unsubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("unsubscribe"); ?></option>
                                       <option value="post_back" id="resubscribe_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'RESUBSCRIBE_QUICK_BOXER') echo 'selected'; ?> ><?php echo $this->lang->line("re-subscribe"); ?></option>
                                    
                                     <option value="post_back" id="human_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'YES_START_CHAT_WITH_HUMAN') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Human"); ?></option>
                                       <option value="post_back" id="robot_postback" <?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['type']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'postback' && isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'selected'; ?> ><?php echo $this->lang->line("Chat with Robot"); ?></option>
                                 </select>
                              </div>
                           </div>
                           <div class="col-4">
                              <div class="form-group" id="list_with_button_postid_div_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) || $full_message[$k]['attachment']['payload']['buttons'][0]['type'] != 'postback' || $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'UNSUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'RESUBSCRIBE_QUICK_BOXER' || $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'YES_START_CHAT_WITH_HUMAN' || $full_message[$k]['attachment']['payload']['buttons'][0]['payload'] == 'YES_START_CHAT_WITH_BOT') echo 'style="display: none;"'; ?> >
                                 <label><?php echo $this->lang->line("PostBack id"); ?></label>
                                 <?php $pname="list_with_button_post_id_".$k; ?>
                                 <?php 
                                 $pdefault=(isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'postback') ? $full_message[$k]['attachment']['payload']['buttons'][0]['payload']:"";
                                 if($pdefault == 'UNSUBSCRIBE_QUICK_BOXER')
                                   $poption['UNSUBSCRIBE_QUICK_BOXER']=$this->lang->line('unsubscribe');
                                 if($pdefault == 'RESUBSCRIBE_QUICK_BOXER')
                                   $poption['RESUBSCRIBE_QUICK_BOXER']=$this->lang->line('re-subscribe');
                                 if($pdefault == 'YES_START_CHAT_WITH_HUMAN')
                                   $poption['YES_START_CHAT_WITH_HUMAN']=$this->lang->line('Chat with Human');
                                 if($pdefault == 'YES_START_CHAT_WITH_BOT')
                                   $poption['YES_START_CHAT_WITH_BOT']=$this->lang->line('Chat with Robot');
                                 ?>
                                 <?php echo form_dropdown($pname, $poption,$pdefault,'class="form-control push_postback" id="'.$pname.'"'); ?>
                                 <a href="" class="add_template float-left"><i class="fa fa-plus-circle"></i>     <?php echo $this->lang->line("Add");?></a>
                                 <a href="" class="ref_template float-right"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Refresh");?></a>
                              </div>
                              <div class="form-group" id="list_with_button_web_url_div_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][0]['url']) || (isset($full_message[$k]['attachment']['payload']['buttons'][0]['url']) && strpos($full_message[$k]['attachment']['payload']['buttons'][0]['url'],'webview_builder/get_birthdate') !== false)) echo 'style="display: none;"'; ?> >
                                 <label><?php echo $this->lang->line("Web Url"); ?></label>
                                 <input type="text" class="form-control"  name="list_with_button_web_url_<?php echo $k; ?>" id="list_with_button_web_url_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['url'])) echo $full_message[$k]['attachment']['payload']['buttons'][0]['url']; ?>" >
                              </div>
                              <div class="form-group" id="list_with_button_call_us_div_<?php echo $k; ?>" <?php if(!isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) || $full_message[$k]['attachment']['payload']['buttons'][0]['type'] != 'phone_number') echo 'style="display: none;"'; ?> >
                                 <label><?php echo $this->lang->line("Phone Number"); ?></label>
                                 <input type="text" class="form-control"  name="list_with_button_call_us_<?php echo $k; ?>" id="list_with_button_call_us_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['attachment']['payload']['buttons'][0]['payload']) && $full_message[$k]['attachment']['payload']['buttons'][0]['type'] == 'phone_number') echo $full_message[$k]['attachment']['payload']['buttons'][0]['payload']; ?>" >
                              </div>
                           </div>
                        </div>
                     </div>

                     <?php for ($j=1; $j <=4 ; $j++) : ?>
                        <div class="col-12" id="list_div_<?php echo $j; ?>_<?php echo $k; ?>" style="<?php if(!isset($full_message[$k]['attachment']['payload']['elements'][$j-1])) echo 'display: none;'; ?> padding-top: 20px;"> 
                           <div style="border: 1px dashed #ccc; background:#fcfcfc;padding:10px 15px;">
                              <div class="row">
                                 <div class="col-12 col-md-6">
                                    <div class="form-group">
                                       <label><?php echo $this->lang->line("Please provide your reply image"); ?></label>

                                       <span class="badge badge-status blue load_preview_modal float-right" item_type="image" file_path="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'list' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'];?>"><i class="fa fa-eye"></i> <?php echo $this->lang->line('preview'); ?></span>

                                       <input type="hidden" class="form-control"  name="list_image_<?php echo $j; ?>_<?php echo $k; ?>" id="list_image_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'list' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['image_url'];?>" />
                                       <div id="list_imageupload_<?php echo $j; ?>_<?php echo $k; ?>"><?php echo $this->lang->line('upload'); ?></div>
                                    </div>                         
                                 </div>
                                 <div class="col-12 col-md-6">
                                    <div class="form-group">
                                       <label><?php echo $this->lang->line("image click destination link"); ?></label>
                                       <input type="text" class="form-control"  name="list_image_destination_link_<?php echo $j; ?>_<?php echo $k; ?>" id="list_image_destination_link_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'list' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['default_action']['url'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['default_action']['url'];?>" />
                                    </div> 
                                 </div>                      
                              </div>

                              <div class="row">
                                 <div class="col-12 col-md-6">
                                    <div class="form-group">
                                       <label><?php echo $this->lang->line("title"); ?></label>
                                       <input type="text" class="form-control"  name="list_title_<?php echo $j; ?>_<?php echo $k; ?>" id="list_title_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'list' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['title'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['title'];?>" />
                                    </div>
                                 </div>  
                                 <div class="col-12 col-md-6">
                                    <div class="form-group">
                                       <label><?php echo $this->lang->line("sub-title"); ?></label>
                                       <input type="text" class="form-control"  name="list_subtitle_<?php echo $j; ?>_<?php echo $k; ?>" id="list_subtitle_<?php echo $j; ?>_<?php echo $k; ?>" value="<?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'list' && isset($full_message[$k]['attachment']['payload']['elements'][$j-1]['subtitle'])) echo $full_message[$k]['attachment']['payload']['elements'][$j-1]['subtitle'];?>" />
                                    </div>
                                 </div>  
                              </div>
                           </div>
                        </div>
                     <?php endfor; ?>

                     <div class="col-12 clearfix">
                        <button <?php if(isset($full_message[$k]['template_type']) && $full_message[$k]['template_type'] == 'list' && count($full_message[$k]['attachment']['payload']['elements']) == 4) echo "style='display : none;'"; ?> id="list_template_add_button_<?php echo $k; ?>" class="btn btn-sm btn-outline-primary float-right no_radius"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line("add more template");?></button>
                     </div>

                  </div>

                  </div> <!-- end of empty style div -->
                </div> <!-- end of card body  -->
              </div>
          <?php } ?>
                
                <div class="row">
                  <div class="col-12 clearfix">
                    <button id="multiple_template_add_button" class="btn btn-outline-primary float-right no_radius" <?php if($active_reply_count==3) echo 'style="display: none;"'; ?> ><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('add more reply'); ?></button>
                  </div>
                </div>
                <br/><br/>
                <div class="row">
                  <div class="col-6">
                     <button id="submit" class="btn btn-lg btn-primary"><i class="fa fa-send"></i> <?php echo $this->lang->line('Update'); ?></button>
                  </div>
                  <?php if($default_template == '0') : ?>
                  <div class="col-6">
                     <a class="btn btn-lg btn-secondary float-right" href="<?php echo base_url("messenger_bot/bot_settings/").$page_info['id'].'/1'; ?>"><i class="fas fa-times"></i> <?php echo $this->lang->line('Back'); ?></a>
                  </div>  
                  <?php elseif($default_template == 'postback') : ?>
                  <div class="col-6">
                     <a class="btn btn-lg btn-secondary float-right" href="<?php echo base_url("messenger_bot/template_manager"); ?>"><i class="fas fa-step-backward"></i> <?php echo $this->lang->line('Back'); ?></a>
                  </div>
                  <?php elseif($default_template == 'errlog') : ?>
                  <div class="col-6">
                     <a class="btn btn-lg btn-secondary float-right" href="<?php echo base_url("messenger_bot/bot_list"); ?>"><i class="fas fa-step-backward"></i> <?php echo $this->lang->line('Back'); ?></a>
                  </div>
                  <?php endif; ?>                
                </div>
              </form>
            </div>
            
            <?php if($iframe!="1") : ?>
            <div class="hidden-xs hidden-sm col-md-3 img_holder">
              <div id="text_preview_div" style="">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/text.png')) echo site_url()."assets/images/preview/text.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/text.png"; ?>" class="img-rounded" alt="Text Preview"></center>
              </div>

              <div id="image_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/image.png')) echo site_url()."assets/images/preview/image.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/image.png"; ?>" class="img-rounded" alt="Image Preview"></center>
              </div>

              <div id="audio_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/mp3.png')) echo site_url()."assets/images/preview/mp3.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/mp3.png"; ?>" class="img-rounded" alt="Audio Preview"></center>
              </div>

              <div id="video_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/video.png')) echo site_url()."assets/images/preview/video.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/video.png"; ?>" class="img-rounded" alt="Video Preview"></center>
              </div>

              <div id="file_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/file.png')) echo site_url()."assets/images/preview/file.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/file.png"; ?>" class="img-rounded" alt="File Preview"></center>
              </div>

              <div id="quick_reply_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/quick_reply.png')) echo site_url()."assets/images/preview/quick_reply.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/quick_reply.png"; ?>" class="img-rounded" alt="Quick Reply Preview"></center>
              </div>

              <div id="text_with_buttons_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/button.png')) echo site_url()."assets/images/preview/button.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/button.png"; ?>" class="img-rounded" alt="Text With Buttons Preview"></center>
              </div>

              <div id="generic_template_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/generic.png')) echo site_url()."assets/images/preview/generic.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/generic.png"; ?>" class="img-rounded" alt="Generic Template Preview"></center>
              </div>

              <div id="carousel_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/carousel.png')) echo site_url()."assets/images/preview/carousel.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/carousel.png"; ?>" class="img-rounded" alt="Carousel Template Preview"></center>
              </div>

              <div id="list_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/list.png')) echo site_url()."assets/images/preview/list.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/list.png"; ?>" class="img-rounded" alt="List Template Preview"></center>
              </div>

              <div id="media_preview_div" style="display: none;">
                <center><img src="<?php if(file_exists(FCPATH.'assets/images/preview/media.png')) echo site_url()."assets/images/preview/media.png"; else echo "https://mysitespy.net/2waychat_demo/msgbot_demo/preview/media.png"; ?>" class="img-rounded" alt="Media Template Preview"></center>
              </div>

            </div>
            <?php endif; ?>
           
          </div>
          <br>
          <div id="submit_status" class="text-center"></div>
    

      </div> <!-- end of card body -->
    </div>

<?php if($iframe!='1') : ?>
</section>   
<?php endif; ?>


  <br>
  <?php if($this->session->flashdata('bot_success')===1) { ?>
  <div class="alert alert-success text-center" id="bot_success"><i class="fa fa-check"></i> <?php echo $this->lang->line("Bot settings has been updated successfully.");?></div>
  <?php } ?>



<?php 
$areyousure=$this->lang->line("are you sure"); 
$somethingwentwrong = $this->lang->line("something went wrong.");  
$doyoureallywanttodeletethisbot = $this->lang->line("do you really want to delete this bot?");
?>

<script type="text/javascript">

$(document).ready(function(){
	$("#text_reply_1, #text_reply_2, #text_reply_3, #quick_reply_text_1, #quick_reply_text_2, #quick_reply_text_3, #text_with_buttons_input_1, #text_with_buttons_input_2, #text_with_buttons_input_3").emojioneArea({
			autocomplete: false,
			pickerPosition: "bottom"
	  });

  setTimeout(function(){$("#loader").hide();}, 3000);

  // getting postback list and making iframe
  var iframe_link="<?php echo base_url('messenger_bot/create_new_template/1/');?><?php echo $page_info['id'];?>";
  $('#add_template_modal').on('shown.bs.modal',function(){ 
    $(this).find('iframe').attr('src',iframe_link);
  });
  	

});



  $(document).ready(function(e){

    $(".quick_reply_button_type_class, .media_type_class, .text_with_button_type_class, .generic_template_button_type_class, .carousel_button_type_class, .list_with_button_type_class").select2({
          width: '100%'
        });

    $(document).on('click','.media_template_modal',function(){
       $("#media_template_modal").modal();
    });

    // $("#keywordtype_postback_id").select2();

    $(document).on('click','.load_preview_modal',function(e){
      e.preventDefault();
      var item_type = $(this).attr('item_type');
      var file_path = $(this).next().val();
      $("#preview_text_field").val(file_path);
      if(item_type == 'image')
      {
        $("#modal_preview_image").attr('src',file_path);
        $("#image_preview_div_modal").show();
        $("#video_preview_div_modal").hide();
        $("#audio_preview_div_modal").hide();
        
      }
      if(item_type == 'video')
      {
        var html_content = "<source src='"+file_path+"' type='video/mp4'>";
        $("#modal_preview_video").html(html_content);
        $("#image_preview_div_modal").hide();
        $("#audio_preview_div_modal").hide();
        $("#video_preview_div_modal").show();
      }
      if(item_type == 'audio')
      {
        var html_content = "<source src='"+file_path+"' type='audio/ogg'>";
        $("#modal_preview_audio").html(html_content);
        $("#image_preview_div_modal").hide();
        $("#video_preview_div_modal").hide();
        $("#audio_preview_div_modal").show();
      }
      $("#modal_for_preview").modal();
    });

    $( document ).on( 'click', '.bs-dropdown-to-select-group .dropdown-menu li', function( event ) {
      var $target = $( event.currentTarget );
      $target.closest('.bs-dropdown-to-select-group')
      .find('[data-bind="bs-drp-sel-value"]').val($target.attr('data-value'))
      .end()
      .children('.dropdown-toggle').dropdown('toggle');
      $target.closest('.bs-dropdown-to-select-group')
      .find('[data-bind="bs-drp-sel-label"]').text($target.context.textContent);
      return false;
    });
  });

  var multiple_template_add_button_counter = <?php echo $active_reply_count; ?>;
  $(document).on('click','#multiple_template_add_button',function(e){
    e.preventDefault();
    multiple_template_add_button_counter++
    $("#multiple_template_div_"+multiple_template_add_button_counter).show();
    $("#multiple_template_div_"+multiple_template_add_button_counter).find(".remove_reply").show();
    if(multiple_template_add_button_counter == 3){
      var previous_div_id_counter = multiple_template_add_button_counter-1;
      $("#multiple_template_div_"+previous_div_id_counter).find(".remove_reply").hide();
      $("#multiple_template_add_button").hide();
    }
  });


  $(document).on('click','.remove_reply',function(){
    var remove_reply_counter_variable = "multiple_template_add_button_counter";
    var remove_reply_row_id = $(this).attr('row_id');
    $("#"+remove_reply_row_id).find('textarea,input,select').val('');

    $("#"+remove_reply_row_id).hide();
    eval(remove_reply_counter_variable+"--");
    var temp = eval(remove_reply_counter_variable);
    if(temp != 1)
    {
      $("#multiple_template_div_"+temp).find(".remove_reply").show();
    }
    if(temp < 3) $("#multiple_template_add_button").show();
  });

  $(document).on('click','.lead_first_name',function(){
  
  	var textAreaTxt = $(this).parent().next().next().next().children('.emojionearea-editor').html();
		
		var lastIndex = textAreaTxt.lastIndexOf("<br>");
		
		if(lastIndex!='-1')
			textAreaTxt = textAreaTxt.substring(0, lastIndex);
			
	    var txtToAdd = " #LEAD_USER_FIRST_NAME# ";
	    var new_text = textAreaTxt + txtToAdd;
	   	$(this).parent().next().next().next().children('.emojionearea-editor').html(new_text);
	   	$(this).parent().next().next().next().children('.emojionearea-editor').click();
  });

  $(document).on('click','.lead_last_name',function(){

 		var textAreaTxt = $(this).parent().next().next().next().next().children('.emojionearea-editor').html();
		
		var lastIndex = textAreaTxt.lastIndexOf("<br>");
		
		if(lastIndex!='-1')
			textAreaTxt = textAreaTxt.substring(0, lastIndex);
			
	    var txtToAdd = " #LEAD_USER_LAST_NAME# ";
		var new_text = textAreaTxt + txtToAdd;
	   $(this).parent().next().next().next().next().children('.emojionearea-editor').html(new_text);
	   $(this).parent().next().next().next().next().children('.emojionearea-editor').click();
		   
  });
  
</script>


<script type="text/javascript">

  var user_id = "<?php echo $this->session->userdata('user_id'); ?>";
  var base_url="<?php echo site_url(); ?>";
  var areyousure="<?php echo $areyousure;?>";
  var js_array = [<?php echo '"'.implode('","', $total_postback_id_array ).'"' ?>];

  
  var keyword_type = $("input[name=keyword_type]").val();
  if(keyword_type == 'reply')
  {
    $("#keywords_div").show();
  }else{
    $("#keywords_div").hide();
  }

  $(document).on('change','input[name=keyword_type]',function(){
    if($("input[name=keyword_type]").val()=="reply")
    {
      $("#keywords_div").show();
    }
    else 
    {
      $("#keywords_div").hide();
    }
  });


  var keyword_type = $("input[name=keyword_type]").val();
  if(keyword_type == 'post-back')
  {
    $("#postback_div").show();
  }

  $(document).on('change','input[name=keyword_type]',function(){    
    if($("input[name=keyword_type]").val()=="post-back")
    {
      $("#postback_div").show();
    }
    else 
    {
      $("#postback_div").hide();
    }
  });


  var image_upload_limit = "<?php echo $image_upload_limit; ?>";
  var video_upload_limit = "<?php echo $video_upload_limit; ?>";
  var audio_upload_limit = "<?php echo $audio_upload_limit; ?>";
  var file_upload_limit = "<?php echo $file_upload_limit; ?>";

  <?php for($template_type=1;$template_type<=3;$template_type++){ ?>

      var template_type_order="#template_type_<?php echo $template_type ?>";

      $("#image_reply_<?php echo $template_type; ?>").uploadFile({
        url:base_url+"messenger_bot/upload_image_only",
        fileName:"myfile",
        maxFileSize:image_upload_limit*1024*1024,
        showPreview:false,
        returnType: "json",
        dragDrop: true,
        showDelete: true,
        multiple:false,
        maxFileCount:1, 
        acceptFiles:".png,.jpg,.jpeg,.JPEG,.JPG,.PNG,.gif,.GIF",
        deleteCallback: function (data, pd) {
            var delete_url="<?php echo site_url('messenger_bot/delete_uploaded_file');?>";
            $.post(delete_url, {op: "delete",name: data},
                function (resp,textStatus, jqXHR) {
                  $("#image_reply_field_<?php echo $template_type; ?>").val('');  
                  $("#image_reply_div_<?php echo $template_type; ?>").hide();                     
                });
           
         },
         onSuccess:function(files,data,xhr,pd)
           {
               var data_modified = base_url+"upload/image/"+user_id+"/"+data;
               $("#image_reply_field_<?php echo $template_type; ?>").val(data_modified);   
               $("#image_reply_div_<?php echo $template_type; ?>").show().attr('src',data_modified);   
           }
      });


      $("#video_reply_<?php echo $template_type; ?>").uploadFile({
        url:base_url+"messenger_bot/upload_live_video",
        fileName:"myfile",
        maxFileSize:video_upload_limit*1024*1024,
        showPreview:false,
        returnType: "json",
        dragDrop: true,
        showDelete: true,
        multiple:false,
        maxFileCount:1, 
        acceptFiles:".flv,.mp4,.wmv,.WMV,.MP4,.FLV",
        deleteCallback: function (data, pd) {
          var delete_url="<?php echo site_url('messenger_bot/delete_uploaded_live_file');?>";
          $.post(delete_url, {op: "delete",name: data},
            function (resp,textStatus, jqXHR) {  
                $("#video_reply_field_<?php echo $template_type; ?>").val('');  
                $("#video_tag_<?php echo $template_type; ?>").hide();             
            });

        },
        onSuccess:function(files,data,xhr,pd)
        {
          var file_path = base_url+"upload/video/"+data;
          $("#video_reply_field_<?php echo $template_type; ?>").val(file_path);   
          $("#video_tag_<?php echo $template_type; ?>").show();
          $("#video_reply_div_<?php echo $template_type; ?>").attr('src',file_path); 
        }
      });

      $("#audio_reply_<?php echo $template_type; ?>").uploadFile({
        url:base_url+"messenger_bot/upload_audio_file",
        fileName:"myfile",
        maxFileSize:audio_upload_limit*1024*1024,
        showPreview:false,
        returnType: "json",
        dragDrop: true,
        showDelete: true,
        multiple:false,
        maxFileCount:1, 
        acceptFiles:".amr,.mp3,.wav,.WAV,.MP3,.AMR",
        deleteCallback: function (data, pd) {
          var delete_url="<?php echo site_url('messenger_bot/delete_audio_file');?>";
          $.post(delete_url, {op: "delete",name: data},
            function (resp,textStatus, jqXHR) {  
                $("#audio_reply_field_<?php echo $template_type; ?>").val('');  
                $("#audio_tag_<?php echo $template_type; ?>").hide();             
            });

        },
        onSuccess:function(files,data,xhr,pd)
        {
          var file_path = base_url+"upload/audio/"+data;
          $("#audio_reply_field_<?php echo $template_type; ?>").val(file_path);   
          $("#audio_tag_<?php echo $template_type; ?>").show();
          $("#audio_reply_div_<?php echo $template_type; ?>").attr('src',file_path); 
        }
      });

      $("#file_reply_<?php echo $template_type; ?>").uploadFile({
        url:base_url+"messenger_bot/upload_general_file",
        fileName:"myfile",
        maxFileSize:file_upload_limit*1024*1024,
        showPreview:false,
        returnType: "json",
        dragDrop: true,
        
        showDelete: true,
        multiple:false,
        maxFileCount:1, 
        acceptFiles:".doc,.docx,.pdf,.txt,.ppt,.pptx,.xls,.xlsx,.DOC,.DOCX,.PDF,.TXT,.PPT,.PPTX,.XLS,.XLSX",
        deleteCallback: function (data, pd) {
          var delete_url="<?php echo site_url('messenger_bot/delete_general_file');?>";
          $.post(delete_url, {op: "delete",name: data},
            function (resp,textStatus, jqXHR) {  
                $("#file_reply_field_<?php echo $template_type; ?>").val('');            
            });

        },
        onSuccess:function(files,data,xhr,pd)
        {
          var file_path = base_url+"upload/file/"+data;
          $("#file_reply_field_<?php echo $template_type; ?>").val(file_path);   
        }
      });


      $("#generic_image_<?php echo $template_type; ?>").uploadFile({
        url:base_url+"messenger_bot/upload_image_only",
        fileName:"myfile",
        maxFileSize:image_upload_limit*1024*1024,
        showPreview:false,
        returnType: "json",
        dragDrop: true,
        showDelete: true,
        multiple:false,
        maxFileCount:1, 
        acceptFiles:".png,.jpg,.jpeg,.JPEG,.JPG,.PNG,.gif,.GIF",
        deleteCallback: function (data, pd) {
            var delete_url="<?php echo site_url('messenger_bot/delete_uploaded_file');?>";
            $.post(delete_url, {op: "delete",name: data},
                function (resp,textStatus, jqXHR) {
                  $("#generic_template_image_<?php echo $template_type; ?>").val('');                   
                });
           
         },
         onSuccess:function(files,data,xhr,pd)
           {
               var data_modified = base_url+"upload/image/"+user_id+"/"+data;
               $("#generic_template_image_<?php echo $template_type; ?>").val(data_modified);     
           }
      });

  
      <?php for($i=1; $i<=10; $i++) : ?>
        $("#generic_imageupload_<?php echo $i; ?>_<?php echo $template_type; ?>").uploadFile({
          url:base_url+"messenger_bot/upload_image_only",
          fileName:"myfile",
          maxFileSize:image_upload_limit*1024*1024,
          showPreview:false,
          returnType: "json",
          dragDrop: true,
          showDelete: true,
          multiple:false,
          maxFileCount:1, 
          acceptFiles:".png,.jpg,.jpeg,.JPEG,.JPG,.PNG,.gif,.GIF",
          deleteCallback: function (data, pd) {
              var delete_url="<?php echo site_url('messenger_bot/delete_uploaded_file');?>";
              $.post(delete_url, {op: "delete",name: data},
                  function (resp,textStatus, jqXHR) {
                    $("#carousel_image_<?php echo $i; ?>_<?php echo $template_type; ?>").val('');                      
                  });
             
           },
           onSuccess:function(files,data,xhr,pd)
             {
                 var data_modified = base_url+"upload/image/"+user_id+"/"+data;
                 $("#carousel_image_<?php echo $i; ?>_<?php echo $template_type; ?>").val(data_modified);     
             }
        });
      <?php endfor; ?>

      <?php for($i=1; $i<=4; $i++) : ?>
        $("#list_imageupload_<?php echo $i; ?>_<?php echo $template_type; ?>").uploadFile({
          url:base_url+"messenger_bot/upload_image_only",
          fileName:"myfile",
          maxFileSize:image_upload_limit*1024*1024,
          showPreview:false,
          returnType: "json",
          dragDrop: true,
          showDelete: true,
          multiple:false,
          maxFileCount:1, 
          acceptFiles:".png,.jpg,.jpeg,.JPEG,.JPG,.PNG,.gif,.GIF",
          deleteCallback: function (data, pd) {
              var delete_url="<?php echo site_url('messenger_bot/delete_uploaded_file');?>";
              $.post(delete_url, {op: "delete",name: data},
                  function (resp,textStatus, jqXHR) {
                    $("#list_image_<?php echo $i; ?>_<?php echo $template_type; ?>").val('');                      
                  });
             
           },
           onSuccess:function(files,data,xhr,pd)
             {
                 var data_modified = base_url+"upload/image/"+user_id+"/"+data;
                 $("#list_image_<?php echo $i; ?>_<?php echo $template_type; ?>").val(data_modified);     
             }
        });
      <?php endfor; ?>
 
      $("document").ready(function(){
        var selected_template = $("#template_type_<?php echo $template_type ?>").val();
        selected_template = selected_template.replace(/ /gi, "_");

        var template_type_array = ['text','image','audio','video','file','quick_reply','text_with_buttons','generic_template','carousel','list','media'];
        template_type_array.forEach(templates_hide_show_function);
        function templates_hide_show_function(item, index)
        {
          var template_type_preview_div_name = "#"+item+"_preview_div";

          var template_type_div_name = "#"+item+"_div_<?php echo $template_type; ?>";
          if(selected_template == item){
            $(template_type_div_name).show();
            $(template_type_preview_div_name).show();
          }
          else{
            $(template_type_div_name).hide();
            $(template_type_preview_div_name).hide();
          }

          if(selected_template == 'quick_reply')
          {
            $("#quick_reply_row_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template == 'media')
          {
            $("#media_row_1_<?php echo $template_type; ?>").show();     
          }

          if(selected_template == 'text_with_buttons')
          {
            $("#text_with_buttons_row_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template == 'generic_template')
          {
            $("#generic_template_row_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template == 'carousel')
          {
            $("#carousel_div_1_<?php echo $template_type; ?>").show();
            for (var i = 1; i <= 10; i++) 
            {
              $("#carousel_row_"+i+"_1_<?php echo $template_type; ?>").show();
            }
          }

          if(selected_template == 'list')
          {
            $("#list_div_1_<?php echo $template_type; ?>").show();
            $("#list_div_2_<?php echo $template_type; ?>").show();
          }

        }
      });


      $(document).on('change',"#template_type_<?php echo $template_type ?>",function(){
      
        var selected_template_on_change = $("#template_type_<?php echo $template_type ?>").val();
        selected_template_on_change = selected_template_on_change.replace(/ /gi, "_");

        var template_type_array = ['text','image','audio','video','file','quick_reply','text_with_buttons','generic_template','carousel','list','media'];
        template_type_array.forEach(templates_hide_show_function);
        function templates_hide_show_function(item, index)
        {
          var template_type_preview_div_name = "#"+item+"_preview_div";

          var template_type_div_name = "#"+item+"_div_<?php echo $template_type; ?>";
          if(selected_template_on_change == item){
            $(template_type_div_name).show();
            $(template_type_preview_div_name).show();
          }
          else{
            $(template_type_div_name).hide();
            $(template_type_preview_div_name).hide();
          }

          if(selected_template_on_change == 'quick_reply')
          {
            $("#quick_reply_row_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template_on_change == 'media')
          {
            $("#media_row_1_<?php echo $template_type; ?>").show();     
          }

          if(selected_template_on_change == 'text_with_buttons')
          {
            $("#text_with_buttons_row_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template_on_change == 'generic_template')
          {
            $("#generic_template_row_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template_on_change == 'carousel')
          {
            $("#carousel_div_1_<?php echo $template_type; ?>").show();
            $("#carousel_row_1_1_<?php echo $template_type; ?>").show();
          }

          if(selected_template_on_change == 'list')
          {
            $("#list_div_1_<?php echo $template_type; ?>").show();
            $("#list_div_2_<?php echo $template_type; ?>").show();
          }

        }
      });



      var media_counter_<?php echo $template_type; ?> = "<?php if (isset($full_message[$template_type]['attachment']['payload']['elements'][0]['buttons'])) echo count($full_message[$template_type]['attachment']['payload']['elements'][0]['buttons']); else echo 1; ?>";
  
     $(document).on('click',"#media_add_button_<?php echo $template_type; ?>",function(e){
        e.preventDefault();

        var button_id = media_counter_<?php echo $template_type; ?>;
        var media_text = "#media_text_"+button_id+"_<?php echo $template_type; ?>";
        var media_type = "#media_type_"+button_id+"_<?php echo $template_type; ?>";

        var media_text_check = $(media_text).val();
        if(media_text_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
          return;
        }

        var media_type_check = $(media_type).val();
        if(media_type_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
          return;
        }else if(media_type_check == 'post_back'){

          var media_post_id = "#media_post_id_"+button_id+"_<?php echo $template_type; ?>";
          var media_post_id_check = $(media_post_id).val();
          if(media_post_id_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
            return;
          }
          
        }else if(media_type_check == 'web_url' || media_type_check == 'web_url_compact' || media_type_check == 'web_url_tall' || media_type_check == 'web_url_full'){
          var media_web_url = "#media_web_url_"+button_id+"_<?php echo $template_type; ?>";
          var media_web_url_check = $(media_web_url).val();
          if(media_web_url_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
            return;
          }
        }else if(media_type_check == 'phone_number'){
          var media_call_us = "#media_call_us_"+button_id+"_<?php echo $template_type; ?>";
          var media_call_us_check = $(media_call_us).val();
          if(media_call_us_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
            return;
          }
        }

        media_counter_<?php echo $template_type; ?>++;

        // remove button hide for current div and show for next div
        $(media_type).parent().parent().next().next().hide();
        var next_item_remove_parent_div = $(media_type).parent().parent().parent().next().attr('id');
        $("#"+next_item_remove_parent_div+" div:last").show();

        var x=media_counter_<?php echo $template_type; ?>;
        $("#media_row_"+x+"_<?php echo $template_type; ?>").show();
        if(media_counter_<?php echo $template_type; ?> == 3)
          $("#media_add_button_<?php echo $template_type; ?>").hide();
      });




      var quick_reply_button_counter_<?php echo $template_type; ?> = "<?php if (isset($full_message[$template_type]['quick_replies'])) echo count($full_message[$template_type]['quick_replies']); else echo 1; ?>";

    
      $(document).on('click',"#quick_reply_add_button_<?php echo $template_type; ?>",function(e){
        e.preventDefault();
      
        var button_id = quick_reply_button_counter_<?php echo $template_type; ?>;      
        var quick_reply_button_text = "#quick_reply_button_text_"+button_id+"_<?php echo $template_type; ?>";
        var quick_reply_post_id = "#quick_reply_post_id_"+button_id+"_<?php echo $template_type; ?>";
        var quick_reply_button_type = "#quick_reply_button_type_"+button_id+"_<?php echo $template_type; ?>";

        quick_reply_button_type = $(quick_reply_button_type).val();

        var quick_reply_post_id_check = $(quick_reply_post_id).val();

        if(quick_reply_button_type == 'post_back')
        {        
          if(quick_reply_post_id_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
            return;
          }

        }

        if(quick_reply_button_type == '')
        {
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
          return;
        }

        

        var quick_reply_button_text_check = $(quick_reply_button_text).val();
        if(quick_reply_button_type == 'post_back')
        { 
          if(quick_reply_button_text_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
            return;
          }
        }

        quick_reply_button_counter_<?php echo $template_type; ?>++;

        // remove button hide for current div and show for next div
        var div_id = "#quick_reply_button_type_"+button_id+"_<?php echo $template_type; ?>";
        $(div_id).parent().parent().next().next().hide();
        var next_item_remove_parent_div = $(div_id).parent().parent().parent().next().attr('id');
        $("#"+next_item_remove_parent_div+" div:last").show();
      
        var x=  quick_reply_button_counter_<?php echo $template_type; ?>;
        $("#quick_reply_row_"+x+"_<?php echo $template_type; ?>").show();
      
        if(quick_reply_button_counter_<?php echo $template_type; ?> == 11)
          $("#quick_reply_add_button_<?php echo $template_type; ?>").hide();

      });



     var text_with_button_counter_<?php echo $template_type; ?> = "<?php if (isset($full_message[$template_type]['attachment']['payload']['buttons'])) echo count($full_message[$template_type]['attachment']['payload']['buttons']); else echo 1; ?>";
  
     $(document).on('click',"#text_with_button_add_button_<?php echo $template_type; ?>",function(e){
        e.preventDefault();

        var button_id = text_with_button_counter_<?php echo $template_type; ?>;
        var text_with_buttons_text = "#text_with_buttons_text_"+button_id+"_<?php echo $template_type; ?>";
        var text_with_button_type = "#text_with_button_type_"+button_id+"_<?php echo $template_type; ?>";

        var text_with_buttons_text_check = $(text_with_buttons_text).val();
        if(text_with_buttons_text_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
          return;
        }

        var text_with_button_type_check = $(text_with_button_type).val();
        if(text_with_button_type_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
          return;
        }else if(text_with_button_type_check == 'post_back'){

          var text_with_button_post_id = "#text_with_button_post_id_"+button_id+"_<?php echo $template_type; ?>";
          var text_with_button_post_id_check = $(text_with_button_post_id).val();
          if(text_with_button_post_id_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
            return;
          }

        }else if(text_with_button_type_check == 'web_url' || text_with_button_type_check == 'web_url_compact' || text_with_button_type_check == 'web_url_tall' || text_with_button_type_check == 'web_url_full'){
          var text_with_button_web_url = "#text_with_button_web_url_"+button_id+"_<?php echo $template_type; ?>";
          var text_with_button_web_url_check = $(text_with_button_web_url).val();
          if(text_with_button_web_url_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
            return;
          }
        }else if(text_with_button_type_check == 'phone_number'){
          var text_with_button_call_us = "#text_with_button_call_us_"+button_id+"_<?php echo $template_type; ?>";
          var text_with_button_call_us_check = $(text_with_button_call_us).val();
          if(text_with_button_call_us_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
            return;
          }
        }

        text_with_button_counter_<?php echo $template_type; ?>++;

        // remove button hide for current div and show for next div
        $(text_with_button_type).parent().parent().next().next().hide();
        var next_item_remove_parent_div = $(text_with_button_type).parent().parent().parent().next().attr('id');
        $("#"+next_item_remove_parent_div+" div:last").show();

        var x=text_with_button_counter_<?php echo $template_type; ?>;
        $("#text_with_buttons_row_"+x+"_<?php echo $template_type; ?>").show();
        if(text_with_button_counter_<?php echo $template_type; ?> == 3)
          $("#text_with_button_add_button_<?php echo $template_type; ?>").hide();
      });


     var  generic_with_button_counter_<?php echo $template_type; ?> = "<?php if(isset($full_message[$template_type]['attachment']['payload']['elements'][0]['buttons'])) echo count($full_message[$template_type]['attachment']['payload']['elements'][0]['buttons']); else echo 1; ?>";
  
    $(document).on('click',"#generic_template_add_button_<?php echo $template_type; ?>",function(e){
      e.preventDefault();

      var button_id = generic_with_button_counter_<?php echo $template_type; ?>;
      var generic_template_button_text = "#generic_template_button_text_"+button_id+"_<?php echo $template_type; ?>";
      var generic_template_button_type = "#generic_template_button_type_"+button_id+"_<?php echo $template_type; ?>";

      var generic_template_button_text_check = $(generic_template_button_text).val();
      if(generic_template_button_text_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
        return;
      }

      var generic_template_button_type_check = $(generic_template_button_type).val();
      if(generic_template_button_type_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
        return;
      }else if(generic_template_button_type_check == 'post_back'){

        var generic_template_button_post_id = "#generic_template_button_post_id_"+button_id+"_<?php echo $template_type; ?>";
        var generic_template_button_post_id_check = $(generic_template_button_post_id).val();
        if(generic_template_button_post_id_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
          return;
        }

      }else if(generic_template_button_type_check == 'web_url' || generic_template_button_type_check == 'web_url_compact' || generic_template_button_type_check == 'web_url_tall' || generic_template_button_type_check == 'web_url_full'){

        var generic_template_button_web_url = "#generic_template_button_web_url_"+button_id+"_<?php echo $template_type; ?>";
        var generic_template_button_web_url_check = $(generic_template_button_web_url).val();
        if(generic_template_button_web_url_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
          return;
        }
      }else if(generic_template_button_type_check == 'phone_number'){
        var generic_template_button_call_us = "#generic_template_button_call_us_"+button_id+"_<?php echo $template_type; ?>";
        var generic_template_button_call_us_check = $(generic_template_button_call_us).val();
        if(generic_template_button_call_us_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
          return;
        }
      }

      generic_with_button_counter_<?php echo $template_type; ?>++;

      // remove button hide for current div and show for next div
      $(generic_template_button_type).parent().parent().next().next().hide();
      var next_item_remove_parent_div = $(generic_template_button_type).parent().parent().parent().next().attr('id');
      $("#"+next_item_remove_parent_div+" div:last").show();
    
      var x=generic_with_button_counter_<?php echo $template_type; ?>;
    
      $("#generic_template_row_"+x+"_<?php echo $template_type; ?>").show();
      if(generic_with_button_counter_<?php echo $template_type; ?> == 3)
        $("#generic_template_add_button_<?php echo $template_type; ?>").hide();
      });


    <?php for($j=1; $j<=10; $j++) : ?>
      
       var carousel_add_button_counter_<?php echo $j; ?>_<?php echo $template_type; ?> = "<?php if(isset($full_message[$template_type]['attachment']['payload']['elements'][$j-1]['buttons'])) echo count($full_message[$template_type]['attachment']['payload']['elements'][$j-1]['buttons']); else echo 1; ?>";
    
       $(document).on('click',"#carousel_add_button_<?php echo $j; ?>_<?php echo $template_type; ?>",function(e){
         e.preventDefault();

         var y= carousel_add_button_counter_<?php echo $j; ?>_<?php echo $template_type; ?>;

         var carousel_button_text = "#carousel_button_text_<?php echo $j; ?>_"+y+"_<?php echo $template_type; ?>";
         var carousel_button_type = "#carousel_button_type_<?php echo $j; ?>_"+y+"_<?php echo $template_type; ?>";
    
         var carousel_button_text_check = $(carousel_button_text).val();
         if(carousel_button_text_check == ''){
           swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
           return;
         }

         var carousel_button_type_check = $(carousel_button_type).val();
         if(carousel_button_type_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
           return;
         }else if(carousel_button_type_check == 'post_back'){

           var carousel_button_post_id = "#carousel_button_post_id_<?php echo $j;?>_"+y+"_<?php echo $template_type; ?>";
           var carousel_button_post_id_check = $(carousel_button_post_id).val();
           if(carousel_button_post_id_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
             return;
           }

         }else if(carousel_button_type_check == 'web_url' || carousel_button_type_check == 'web_url_compact' || carousel_button_type_check == 'web_url_full' || carousel_button_type_check == 'web_url_tall'){

           var carousel_button_web_url = "#carousel_button_web_url_<?php echo $j;?>_"+y+"_<?php echo $template_type; ?>";
           var carousel_button_web_url_check = $(carousel_button_web_url).val();
           if(carousel_button_web_url_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
             return;
           }
         }else if(carousel_button_type_check == 'phone_number'){
          var carousel_button_call_us = "#carousel_button_call_us_<?php echo $j;?>_"+y+"_<?php echo $template_type; ?>";
          var carousel_button_call_us_check = $(carousel_button_call_us).val();
          if(carousel_button_call_us_check == ''){
            swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
            return;
          }
        }

          carousel_add_button_counter_<?php echo $j; ?>_<?php echo $template_type; ?> ++;

          // remove button hide for current div and show for next div
          $(carousel_button_type).parent().parent().next().next().hide();
          var next_item_remove_parent_div = $(carousel_button_type).parent().parent().parent().next().attr('id');
          $("#"+next_item_remove_parent_div+" div:last").show();
          
          var x= carousel_add_button_counter_<?php echo $j; ?>_<?php echo $template_type; ?>;
          $("#carousel_row_<?php echo $j; ?>_"+x+"_<?php echo $template_type; ?>").show();
          if(carousel_add_button_counter_<?php echo $j; ?>_<?php echo $template_type; ?> == 3)
            $("#carousel_add_button_<?php echo $j; ?>_<?php echo $template_type; ?>").hide();        

       });
    <?php endfor; ?>
    
    
    var carousel_template_counter_<?php echo $template_type; ?> = "<?php if(isset($full_message[$template_type]['attachment']['payload']['elements'])) echo count($full_message[$template_type]['attachment']['payload']['elements']); else echo 1; ?>";
    
    $(document).on('click','#carousel_template_add_button_<?php echo $template_type; ?>',function(e){
         e.preventDefault();

         var carousel_image = "#carousel_image_"+carousel_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
         var carousel_image_check = $(carousel_image).val();
         
         var carousel_title = "#carousel_title_"+carousel_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
         var carousel_title_check = $(carousel_title).val();
         if(carousel_title_check == ''){
           swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide carousel title')?>", 'warning');
           return;
         }

        var carousel_subtitle = "#carousel_subtitle_"+carousel_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
        var carousel_subtitle_check = $(carousel_subtitle).val();

         var carousel_image_destination_link = "#carousel_image_destination_link_"+carousel_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
         var carousel_image_destination_link_check = $(carousel_image_destination_link).val();
        
         carousel_template_counter_<?php echo $template_type; ?>++;
      
         var x = carousel_template_counter_<?php echo $template_type; ?>;
      
         $("#carousel_div_"+x+"_<?php echo $template_type; ?>").show();
         $("#carousel_row_"+x+"_1"+"_<?php echo $template_type; ?>").show();
         if( carousel_template_counter_<?php echo $template_type; ?> == 10)
           $("#carousel_template_add_button_<?php echo $template_type; ?>").hide();
    });


    var list_template_counter_<?php echo $template_type; ?> = "<?php if(isset($full_message[$template_type]['attachment']['payload']['elements'])) echo count($full_message[$template_type]['attachment']['payload']['elements']); else echo 2; ?>";
  
    $(document).on('click','#list_template_add_button_<?php echo $template_type; ?>',function(e){
      e.preventDefault();

      var list_button_text = "#list_with_buttons_text_<?php echo $template_type; ?>";
      var list_button_type = "#list_with_button_type_<?php echo $template_type; ?>";

      var list_button_text_check = $(list_button_text).val();
      if(list_button_text_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
        return;
      }

      var list_button_type_check = $(list_button_type).val();
      if(list_button_type_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
        return;
      }else if(list_button_type_check == 'post_back'){

        var list_button_post_id = "#list_with_button_post_id_<?php echo $template_type; ?>";
        var list_button_post_id_check = $(list_button_post_id).val();
        if(list_button_post_id_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
          return;
        }
      }else if(list_button_type_check == 'web_url' || list_button_type_check == 'web_url_full' || list_button_type_check == 'web_url_tall' || list_button_type_check == 'web_url_compact'){

        var list_button_web_url = "#list_with_button_web_url_<?php echo $template_type; ?>";
        var list_button_web_url_check = $(list_button_web_url).val();
        if(list_button_web_url_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
          return;
        }
      }else if(list_button_type_check == 'phone_number'){
        var list_button_call_us = "#list_with_button_call_us_<?php echo $template_type; ?>";
        var list_button_call_us_check = $(list_button_call_us).val();
        if(list_button_call_us_check == ''){
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
          return;
        }
      }


      var prev_list_image_counter = eval(list_template_counter_<?php echo $template_type; ?>+"-1");
      var list_image_1 = "#list_image_"+prev_list_image_counter+"_"+<?php echo $template_type; ?>;
      var list_image_check_1 = $(list_image_1).val();
      if(list_image_check_1 == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please provide your reply image')?>", 'warning');
        return;
      }

      var list_image = "#list_image_"+list_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
      var list_image_check = $(list_image).val();
      if(list_image_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please provide your reply image')?>", 'warning');
        return;
      }

      var prev_list_title_counter = eval(list_template_counter_<?php echo $template_type; ?>+"-1");
      var list_title_1 = "#list_title_"+prev_list_title_counter+"_"+<?php echo $template_type; ?>;
      var list_title_check_1 = $(list_title_1).val();
      if(list_title_check_1 == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide list title')?>", 'warning');
        return;
      }

      var list_title = "#list_title_"+list_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
      var list_title_check = $(list_title).val();
      if(list_title_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide list title')?>", 'warning');
        return;
      }

      var prev_list_dest_counter = eval(list_template_counter_<?php echo $template_type; ?>+"-1");
      var list_image_destination_link_1 = "#list_image_destination_link_"+prev_list_dest_counter+"_"+<?php echo $template_type; ?>;
      var list_image_destination_link_check_1 = $(list_image_destination_link_1).val();
      if(list_image_destination_link_check_1 == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Image Click Destination Link')?>", 'warning');
        return;        
      }

      var list_image_destination_link = "#list_image_destination_link_"+list_template_counter_<?php echo $template_type; ?>+"_"+<?php echo $template_type; ?>;
      var list_image_destination_link_check = $(list_image_destination_link).val();
      if(list_image_destination_link_check == ''){
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Image Click Destination Link')?>", 'warning');
        return;        
      }

      list_template_counter_<?php echo $template_type; ?>++;
    
      var x = list_template_counter_<?php echo $template_type; ?>;
    
      $("#list_div_"+x+"_<?php echo $template_type; ?>").show();
      if( list_template_counter_<?php echo $template_type; ?> == 4)
        $("#list_template_add_button_<?php echo $template_type; ?>").hide();
    });




  <?php } ?>



  $(document).ready(function() {

    $(document).on('click','.item_remove',function(){
      var counter_variable = $(this).attr('counter_variable');
      var row_id = $(this).attr('row_id');

      var first_column_id = $(this).attr('first_column_id');
      var second_column_id = $(this).attr('second_column_id');
      var add_more_button_id = $(this).attr('add_more_button_id');

      var item_remove_postback = $(this).attr('third_postback');
      var item_remove_weburl = $(this).attr('third_weburl');
      var item_remove_callus = $(this).attr('third_callus');

      var template_type = $(this).attr('template_type');

      $("#"+first_column_id).val('');
      $("#"+first_column_id).removeAttr('readonly');
      var item_remove_button_type = $("#"+second_column_id).val();
      $("#"+second_column_id).val('');

      if(item_remove_button_type == 'post_back')
      {
        if(item_remove_postback != '')
        $("#"+item_remove_postback).val('');
      }
      else if (item_remove_button_type == 'web_url' || item_remove_button_type == 'web_url_compact' || item_remove_button_type == 'web_url_full' || item_remove_button_type == 'web_url_tall' || item_remove_button_type == 'web_url_birthday')
      {
        if(item_remove_weburl != '')
        $("#"+item_remove_weburl).val('');
      }
      else
      {
        if(item_remove_callus != '')
        $("#"+item_remove_callus).val('');
      }

      $("#"+row_id).hide();
      eval(counter_variable+"--");
      var temp = eval(counter_variable);

      if(temp != 1)
      {        
        var previous_item_remove_div = $("#"+row_id).prev('div').attr('id');
        $("#"+previous_item_remove_div+" div:last").show();
      }
      $(this).parent().hide();

      if(template_type == 'quick_reply')
      {
        if(temp < 11) $("#"+add_more_button_id).show();       
      }
      else
      {
        if(temp < 3) $("#"+add_more_button_id).show();        
      }

    });


    $(document).on('click','.delete_bot',function(){
      var id = $(this).attr('id');
      var somethingwentwrong = "<?php echo $somethingwentwrong; ?>";
      var doyoureallywanttodeletethisbot = "<?php echo $doyoureallywanttodeletethisbot; ?>";
      var ans = confirm(doyoureallywanttodeletethisbot);
      if(ans)
      {
        $.ajax({
           type:'POST' ,
           url: "<?php echo base_url('messenger_bot/delete_bot')?>",
           data: {id:id},
           success:function(response)
           {
            if(response=='1')
            location.reload();
            else
            alertify.alert('<?php echo $this->lang->line("Alert"); ?>',somethingwentwrong,function(){});
           }
        });
      }
    });

    $(document).on('change','.quick_reply_button_type_class',function(){
      var button_type = $(this).val();
      var which_number_is_clicked = $(this).attr('id');
     var which_block_is_clicked="";
    
      which_number_is_clicked_main = which_number_is_clicked.split('_');
      which_number_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 2];
      which_block_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 1];

      if(button_type == 'post_back')
      {
        $("#quick_reply_button_text_"+which_number_is_clicked+"_"+which_block_is_clicked).removeAttr('readonly');
        $("#quick_reply_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
      }
      else
      {
        $("#quick_reply_button_text_"+which_number_is_clicked+"_"+which_block_is_clicked).attr('readonly','readonly');
        $("#quick_reply_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      // alert(which_number_is_clicked);
    });



    $(document).on('change','.media_type_class',function(){
      var button_type = $(this).val();
      var which_number_is_clicked = $(this).attr('id');
      which_number_is_clicked_main = which_number_is_clicked.split('_');
      which_number_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 2];
      var which_block_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 1];

      if(button_type == 'post_back')
      {
        $("#media_post_id_"+which_number_is_clicked+"_"+which_block_is_clicked).val(""); 
        $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#media_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#media_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        var option_id=$(this).children(":selected").attr("id");
        if(option_id=="unsubscribe_postback")
        {
           $("#media_post_id_"+which_number_is_clicked+"_"+which_block_is_clicked).val("UNSUBSCRIBE_QUICK_BOXER"); 
           $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="resubscribe_postback")
        {
           $("#media_post_id_"+which_number_is_clicked+"_"+which_block_is_clicked).val("RESUBSCRIBE_QUICK_BOXER"); 
           $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
         if(option_id=="human_postback")
        {
           $("#media_post_id_"+which_number_is_clicked+"_"+which_block_is_clicked).val("YES_START_CHAT_WITH_HUMAN"); 
           $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="robot_postback")
        {
           $("#media_post_id_"+which_number_is_clicked+"_"+which_block_is_clicked).val("YES_START_CHAT_WITH_BOT"); 
           $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
      }
      else if(button_type == 'web_url' || button_type == 'web_url_compact' || button_type == 'web_url_tall' || button_type == 'web_url_full')
      {
        $("#media_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#media_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      else if(button_type == 'phone_number')
      {
        $("#media_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#media_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      else
      {
        $("#media_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#media_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#media_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      // alert(which_number_is_clicked);
    });





    $(document).on('change','.text_with_button_type_class',function(){
      var button_type = $(this).val();
      var which_number_is_clicked = $(this).attr('id');
      which_number_is_clicked_main = which_number_is_clicked.split('_');
      which_number_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 2];
      var which_block_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 1];

      if(button_type == 'post_back')
      {
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='UNSUBSCRIBE_QUICK_BOXER']").remove();
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='RESUBSCRIBE_QUICK_BOXER']").remove();
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='YES_START_CHAT_WITH_HUMAN']").remove();
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='YES_START_CHAT_WITH_BOT']").remove();
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#text_with_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#text_with_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        var option_id=$(this).children(":selected").attr("id");
        if(option_id=="unsubscribe_postback")
        {
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","UNSUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('unsubscribe');?>")); 

           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" option[value=UNSUBSCRIBE_QUICK_BOXER]").attr('selected','selected');

           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="resubscribe_postback")
        {
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","RESUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('re-subscribe');?>")); 
            $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+"  option[value=RESUBSCRIBE_QUICK_BOXER]").attr('selected','selected');
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="human_postback")
        {
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_HUMAN").text("<?php echo $this->lang->line('Chat with Human');?>")); 

           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" option[value=YES_START_CHAT_WITH_HUMAN]").attr('selected','selected');

           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="robot_postback")
        {
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_BOT").text("<?php echo $this->lang->line('Chat with Robot');?>")); 
            $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+"  option[value=YES_START_CHAT_WITH_BOT]").attr('selected','selected');
           $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
      }
      else if(button_type == 'web_url' || button_type == 'web_url_compact' || button_type == 'web_url_tall' || button_type == 'web_url_full')
      {
        $("#text_with_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#text_with_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      else if(button_type == 'phone_number')
      {
        $("#text_with_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#text_with_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      else
      {
        $("#text_with_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#text_with_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#text_with_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      // alert(which_number_is_clicked);
    });

    $(document).on('change','.generic_template_button_type_class',function(){
      var button_type = $(this).val();
      var which_number_is_clicked = $(this).attr('id');
      which_number_is_clicked_main = which_number_is_clicked.split('_');
      which_number_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 2];
      which_block_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 1];
    
    

      if(button_type == 'post_back')
      {
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='UNSUBSCRIBE_QUICK_BOXER']").remove();
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='RESUBSCRIBE_QUICK_BOXER']").remove();
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='YES_START_CHAT_WITH_HUMAN']").remove();
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option[value='YES_START_CHAT_WITH_BOT']").remove();
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#generic_template_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#generic_template_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        var option_id=$(this).children(":selected").attr("id");

        if(option_id=="unsubscribe_postback")
        {
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","UNSUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('unsubscribe');?>")); 

           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" option[value=UNSUBSCRIBE_QUICK_BOXER]").attr('selected','selected');

           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="resubscribe_postback")
        {
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","RESUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('re-subscribe');?>")); 
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+"  option[value=RESUBSCRIBE_QUICK_BOXER]").attr('selected','selected');
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="human_postback")
        {
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_HUMAN").text("<?php echo $this->lang->line('Chat with Human');?>")); 

           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" option[value=YES_START_CHAT_WITH_HUMAN]").attr('selected','selected');

           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }
        if(option_id=="robot_postback")
        {
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_BOT").text("<?php echo $this->lang->line('Chat with Robot');?>")); 
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+" select option").removeAttr('selected');
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked+"  option[value=YES_START_CHAT_WITH_BOT]").attr('selected','selected');
           $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        }

      }
      else if(button_type == 'web_url' || button_type == 'web_url_compact' || button_type == 'web_url_tall' || button_type == 'web_url_full')
      {
        $("#generic_template_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#generic_template_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      else if(button_type == 'phone_number')
      {
        $("#generic_template_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).show();
        $("#generic_template_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      else
      {
        $("#generic_template_button_postid_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#generic_template_button_web_url_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
        $("#generic_template_button_call_us_div_"+which_number_is_clicked+"_"+which_block_is_clicked).hide();
      }
      // alert(which_number_is_clicked);
    });


    $(document).on('change','.carousel_button_type_class',function(){
      var button_type = $(this).val();
      var which_number_is_clicked = $(this).attr('id');
      which_number_is_clicked = which_number_is_clicked.split('_');
    
      var first = which_number_is_clicked[which_number_is_clicked.length - 2];
      var second = which_number_is_clicked[which_number_is_clicked.length - 3];
    
      var block_template_third= which_number_is_clicked[which_number_is_clicked.length - 1];

      if(button_type == 'post_back')
      {
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option[value='UNSUBSCRIBE_QUICK_BOXER']").remove();
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option[value='RESUBSCRIBE_QUICK_BOXER']").remove();
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option[value='YES_START_CHAT_WITH_HUMAN']").remove();
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option[value='YES_START_CHAT_WITH_BOT']").remove();
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).show();
        $("#carousel_button_web_url_div_"+second+"_"+first+"_"+block_template_third).hide();
        $("#carousel_button_call_us_div_"+second+"_"+first+"_"+block_template_third).hide();
        var option_id=$(this).children(":selected").attr("id");

        if(option_id=="unsubscribe_postback")
        {
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select").append($("<option></option>").attr("value","UNSUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('unsubscribe');?>")); 

           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option").removeAttr('selected');
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" option[value=UNSUBSCRIBE_QUICK_BOXER]").attr('selected','selected');

           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
        }
        if(option_id=="resubscribe_postback")
        {
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select").append($("<option></option>").attr("value","RESUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('re-subscribe');?>")); 
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option").removeAttr('selected');
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+"  option[value=RESUBSCRIBE_QUICK_BOXER]").attr('selected','selected');
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
        }
        if(option_id=="human_postback")
        {
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_HUMAN").text("<?php echo $this->lang->line('Chat with Human');?>")); 

           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option").removeAttr('selected');
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" option[value=YES_START_CHAT_WITH_HUMAN]").attr('selected','selected');

           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
        }
        if(option_id=="robot_postback")
        {
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_BOT").text("<?php echo $this->lang->line('Chat with Robot');?>")); 
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+" select option").removeAttr('selected');
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third+"  option[value=YES_START_CHAT_WITH_BOT]").attr('selected','selected');
           $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
        }

      }
      else if(button_type == 'web_url' || button_type == 'web_url_compact' || button_type == 'web_url_tall' || button_type == 'web_url_full')
      {
        $("#carousel_button_web_url_div_"+second+"_"+first+"_"+block_template_third).show();
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
        $("#carousel_button_call_us_div_"+second+"_"+first+"_"+block_template_third).hide();
      }
      else if(button_type == 'phone_number')
      {
        $("#carousel_button_call_us_div_"+second+"_"+first+"_"+block_template_third).show();
        $("#carousel_button_web_url_div_"+second+"_"+first+"_"+block_template_third).hide();
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
      }
      else
      {
        $("#carousel_button_postid_div_"+second+"_"+first+"_"+block_template_third).hide();
        $("#carousel_button_web_url_div_"+second+"_"+first+"_"+block_template_third).hide();
        $("#carousel_button_call_us_div_"+second+"_"+first+"_"+block_template_third).hide();
      }
      // alert(which_number_is_clicked);
    });

    $(document).on('change','.list_with_button_type_class',function(){
      var button_type = $(this).val();
      var which_number_is_clicked = $(this).attr('id');
      which_number_is_clicked_main = which_number_is_clicked.split('_');
      var which_block_is_clicked = which_number_is_clicked_main[which_number_is_clicked_main.length - 1];

      if(button_type == 'post_back')
      {
        $("#list_with_button_postid_div_"+which_block_is_clicked+" select option[value='UNSUBSCRIBE_QUICK_BOXER']").remove();
        $("#list_with_button_postid_div_"+which_block_is_clicked+" select option[value='RESUBSCRIBE_QUICK_BOXER']").remove();
        $("#list_with_button_postid_div_"+which_block_is_clicked+" select option[value='YES_START_CHAT_WITH_HUMAN']").remove();
        $("#list_with_button_postid_div_"+which_block_is_clicked+" select option[value='YES_START_CHAT_WITH_BOT']").remove();
        $("#list_with_button_postid_div_"+which_block_is_clicked).show();
        $("#list_with_button_web_url_div_"+which_block_is_clicked).hide();
        $("#list_with_button_call_us_div_"+which_block_is_clicked).hide();
        var option_id=$(this).children(":selected").attr("id");
        if(option_id=="unsubscribe_postback")
        {
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","UNSUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('unsubscribe');?>")); 
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").val("UNSUBSCRIBE_QUICK_BOXER"); 
           $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        }
        if(option_id=="resubscribe_postback")
        {
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","RESUBSCRIBE_QUICK_BOXER").text("<?php echo $this->lang->line('re-subscribe');?>")); 
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").val("RESUBSCRIBE_QUICK_BOXER"); 
           $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        }
        if(option_id=="human_postback")
        {
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_HUMAN").text("<?php echo $this->lang->line('Chat with Human');?>")); 
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").val("YES_START_CHAT_WITH_HUMAN"); 
           $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        }
        if(option_id=="robot_postback")
        {
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").append($("<option></option>").attr("value","YES_START_CHAT_WITH_BOT").text("<?php echo $this->lang->line('Chat with Robot');?>")); 
           $("#list_with_button_postid_div_"+which_block_is_clicked+" select").val("YES_START_CHAT_WITH_BOT"); 
           $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        }
      }
      else if(button_type == 'web_url' || button_type == 'web_url_compact' || button_type == 'web_url_tall' || button_type == 'web_url_full')
      {
        $("#list_with_button_web_url_div_"+which_block_is_clicked).show();
        $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        $("#list_with_button_call_us_div_"+which_block_is_clicked).hide();
      }
      else if(button_type == 'phone_number')
      {
        $("#list_with_button_call_us_div_"+which_block_is_clicked).show();
        $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        $("#list_with_button_web_url_div_"+which_block_is_clicked).hide();
      }
      else
      {
        $("#list_with_button_postid_div_"+which_block_is_clicked).hide();
        $("#list_with_button_web_url_div_"+which_block_is_clicked).hide();
        $("#list_with_button_call_us_div_"+which_block_is_clicked).hide();
      }
    });



    $(document).on('click','#submit',function(e){   
      e.preventDefault();
      
      var bot_name = $("#bot_name").val();
      var keyword_type = $("input[name=keyword_type]").val();

      if(typeof($("input[name=keyword_type]").val()) == 'undefined')
      {
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please select a reply type form (Reply/Post-back/No Match/Get Started)')?>", 'warning');
        return;
      }

      if(bot_name == '')
      {
        swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Give Bot Name')?>", 'warning');
        return;
      }


      if(keyword_type == 'post-back')
      {

        if($("#keywordtype_postback_id").val() == '' || typeof($("#keywordtype_postback_id").val()) == 'undefined' || $("#keywordtype_postback_id").val() == null)
        {
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please provide postback id')?>", 'warning');
          return;
        }
      }

      if(keyword_type == 'reply')
      {
        var default_template = "<?php echo $default_template; ?>";
        var keywords_list = $("#keywords_list").val();
        if(keywords_list == '' && default_template == '0')
        {
          swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Keywords In Comma Separated')?>", 'warning');
          return;
        }
      }

      for(var m=1; m<=multiple_template_add_button_counter; m++)
      {
          var template_type = $("#template_type_"+m).val();

          if(template_type == 'text')
          {
            var text_reply = $("#text_reply_"+m).val();
            if(text_reply == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply Message')?>", 'warning');
              return;
            }
          }

          if(template_type == "image")
          {
            var image_reply_field =$("#image_reply_field_"+m).val();
            if(image_reply_field == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply Image')?>", 'warning');
              return;
            }
          }

          if(template_type == "audio")
          {
            var audio_reply_field = $("#audio_reply_field_"+m).val();
            if(audio_reply_field == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply Audio')?>", 'warning');
              return;
            }
          }

          if(template_type == "video")
          {
            var video_reply_field = $("#video_reply_field_"+m).val();
            if(video_reply_field == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply Video')?>", 'warning');
              return;          
            }
          }


          if(template_type == "file")
          {
            var file_reply_field = $("#file_reply_field_"+m).val();
            if(file_reply_field == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply File')?>", 'warning');
              return;          
            }
          }



          if(template_type == "media")
          {
            var media_input = $("#media_input_"+m).val();
            if(media_input == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Media URL')?>", 'warning');
              return;          
            }

            var facebook_url = media_input.match(/business.facebook.com/g);
            var facebook_url2 = media_input.match(/www.facebook.com/g);

            if(facebook_url == null && facebook_url2 == null)
            {
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please provide Facebook content URL as Media URL')?>", 'warning');
              return; 
            }

            var submited_media_counter = eval("media_counter_"+m);

            for(var n=1; n<=submited_media_counter; n++)
            {

              var media_text = "#media_text_"+n+"_"+m;
              var media_type = "#media_type_"+n+"_"+m;

              var media_text_check = $(media_text).val();
              if(media_text_check == ''){
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
                return;
              }

              var media_type_check = $(media_type).val();
              if(media_type_check == ''){
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
                return;
              }else if(media_type_check == 'post_back'){

                var media_post_id = "#media_post_id_"+n+"_"+m;
                var media_post_id_check = $(media_post_id).val();
                if(media_post_id_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
                  return;
                }
                
              }else if(media_type_check == 'web_url' || media_type_check == 'web_url_compact' || media_type_check == 'web_url_tall' || media_type_check == 'web_url_full'){
                var media_web_url = "#media_web_url_"+n+"_"+m;
                var media_web_url_check = $(media_web_url).val();
                if(media_web_url_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
                  return;
                }
              }else if(media_type_check == 'phone_number'){
                var media_call_us = "#media_call_us_"+n+"_"+m;
                var media_call_us_check = $(media_call_us).val();
                if(media_call_us_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
                  return;
                }
              }
            }
            
          }



          if(template_type == "quick reply")
          {
            var quick_reply_text = $("#quick_reply_text_"+m).val();
            if(quick_reply_text == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply Message')?>", 'warning');
              return;
            }
            var submited_quick_reply_button_counter = eval("quick_reply_button_counter_"+m);

            for(var n=1; n<=submited_quick_reply_button_counter; n++)
            {
              var quick_reply_button_text = "#quick_reply_button_text_"+n+"_"+m;
              var quick_reply_post_id = "#quick_reply_post_id_"+n+"_"+m;
              var quick_reply_button_type = "#quick_reply_button_type_"+n+"_"+m;

              quick_reply_button_type = $(quick_reply_button_type).val();

              var quick_reply_post_id_check = $(quick_reply_post_id).val();
              if(quick_reply_button_type == 'post_back')
              {        
                if(quick_reply_post_id_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
                  return;
                }

                var quick_reply_button_text_check = $(quick_reply_button_text).val();

                if(quick_reply_button_text_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
                  return;
                }

              }
              if(quick_reply_button_type == '')
              {
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
                return;
              }
            }    
          }


          if(template_type == "text with buttons")
          {
            var text_with_buttons_input = $("#text_with_buttons_input_"+m).val();
            if(text_with_buttons_input == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Reply Message')?>", 'warning');
              return;          
            }

            var submited_text_with_button_counter = eval("text_with_button_counter_"+m);

            for(var n=1; n<=submited_text_with_button_counter; n++)
            {

              var text_with_buttons_text = "#text_with_buttons_text_"+n+"_"+m;
              var text_with_button_type = "#text_with_button_type_"+n+"_"+m;

              var text_with_buttons_text_check = $(text_with_buttons_text).val();
              if(text_with_buttons_text_check == ''){
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
                return;
              }

              var text_with_button_type_check = $(text_with_button_type).val();
              if(text_with_button_type_check == ''){
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Type')?>", 'warning');
                return;
              }else if(text_with_button_type_check == 'post_back'){

                var text_with_button_post_id = "#text_with_button_post_id_"+n+"_"+m;
                var text_with_button_post_id_check = $(text_with_button_post_id).val();
                if(text_with_button_post_id_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
                  return;
                }

              }else if(text_with_button_type_check == 'web_url' || text_with_button_type_check == 'web_url_compact' || text_with_button_type_check == 'web_url_tall' || text_with_button_type_check == 'web_url_full'){
                var text_with_button_web_url = "#text_with_button_web_url_"+n+"_"+m;
                var text_with_button_web_url_check = $(text_with_button_web_url).val();
                if(text_with_button_web_url_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
                  return;
                }
              }else if(text_with_button_type_check == 'phone_number'){
                var text_with_button_call_us = "#text_with_button_call_us_"+n+"_"+m;
                var text_with_button_call_us_check = $(text_with_button_call_us).val();
                if(text_with_button_call_us_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
                  return;
                }
              }
            }
            
          }

          if(template_type == "generic template")
          {
            var generic_template_image = $("#generic_template_image_"+m).val();  

            var generic_template_title = $("#generic_template_title_"+m).val();
            if(generic_template_title == ''){
              swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please give the title')?>", 'warning');
              return;          
            }

            var generic_template_subtitle = $("#generic_template_subtitle_"+m).val();

            var submited_generic_button_counter = eval("generic_with_button_counter_"+m);
            for(var n=1; n<=submited_generic_button_counter; n++)
            {            
              var generic_template_button_text = "#generic_template_button_text_"+n+"_"+m;
              var generic_template_button_type = "#generic_template_button_type_"+n+"_"+m;

              var generic_template_button_text_check = $(generic_template_button_text).val();
              var generic_template_button_type_check = $(generic_template_button_type).val();

              if(generic_template_button_text_check == ''  && generic_template_button_type_check!=''){
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
                return;
              }

              if(generic_template_button_type_check == 'post_back'){

                var generic_template_button_post_id = "#generic_template_button_post_id_"+n+"_"+m;
                var generic_template_button_post_id_check = $(generic_template_button_post_id).val();
                if(generic_template_button_post_id_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
                  return;
                }

              }else if(generic_template_button_type_check == 'web_url' || generic_template_button_type_check == 'web_url_compact' || generic_template_button_type_check == 'web_url_tall' || generic_template_button_type_check == 'web_url_full'){

                var generic_template_button_web_url = "#generic_template_button_web_url_"+n+"_"+m;
                var generic_template_button_web_url_check = $(generic_template_button_web_url).val();
                if(generic_template_button_web_url_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
                  return;
                }
              }else if(generic_template_button_type_check == 'phone_number'){
                var generic_template_button_call_us = "#generic_template_button_call_us_"+n+"_"+m;
                var generic_template_button_call_us_check = $(generic_template_button_call_us).val();
                if(generic_template_button_call_us_check == ''){
                  swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
                  return;
                }
              }
            }
            
          }


          if(template_type == "carousel")
          {
            var submited_carousel_template_counter = eval("carousel_template_counter_"+m);
            for(var n=1; n<=submited_carousel_template_counter; n++)
            {
              var carousel_image = "#carousel_image_"+n+"_"+m;
              var carousel_image_check = $(carousel_image).val();

              var carousel_title = "#carousel_title_"+n+"_"+m;
              var carousel_title_check = $(carousel_title).val();
              if(carousel_title_check == ''){
                swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide carousel title')?>", 'warning');
                return;
              }

              var carousel_subtitle = "#carousel_subtitle_"+n+"_"+m;
              var carousel_subtitle_check = $(carousel_subtitle).val();

              var carousel_image_destination_link = "#carousel_image_destination_link_"+n+"_"+m;
              var carousel_image_destination_link_check = $(carousel_image_destination_link).val();
            }

            <?php for($j=1; $j<=10; $j++) : ?>
              var submited_carousel_add_button_counter = eval("carousel_add_button_counter_<?php echo $j; ?>_"+m);
              for(var n=1; n<=submited_carousel_add_button_counter; n++)
              {
                var carousel_button_text = "#carousel_button_text_<?php echo $j; ?>_"+n+"_"+m;
                var carousel_button_type = "#carousel_button_type_<?php echo $j; ?>_"+n+"_"+m;

                if($(carousel_button_type).parent().parent().parent().is(":visible"))
                {
                  var carousel_button_text_check = $(carousel_button_text).val();
                  var carousel_button_type_check = $(carousel_button_type).val();

                  if(carousel_button_text_check == '' && carousel_button_type_check!=""){
                    swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Button Text')?>", 'warning');
                    return;
                  }

                  if(carousel_button_type_check == 'post_back'){

                    var carousel_button_post_id = "#carousel_button_post_id_<?php echo $j;?>_"+n+"_"+m;
                    var carousel_button_post_id_check = $(carousel_button_post_id).val();
                    if(carousel_button_post_id_check == ''){
                      swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your PostBack Id')?>", 'warning');
                      return;
                    }
                    
                  }else if(carousel_button_type_check == 'web_url' || carousel_button_type_check == 'web_url_compact' || carousel_button_type_check == 'web_url_full' || carousel_button_type_check == 'web_url_tall'){

                    var carousel_button_web_url = "#carousel_button_web_url_<?php echo $j;?>_"+n+"_"+m;
                    var carousel_button_web_url_check = $(carousel_button_web_url).val();
                    if(carousel_button_web_url_check == ''){
                      swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Web Url')?>", 'warning');
                      return;
                    }
                  }else if(carousel_button_type_check == 'phone_number'){
                    var carousel_button_call_us = "#carousel_button_call_us_<?php echo $j;?>_"+n+"_"+m;
                    var carousel_button_call_us_check = $(carousel_button_call_us).val();
                    if(carousel_button_call_us_check == ''){
                      swal('<?php echo $this->lang->line("Warning"); ?>', "<?php echo $this->lang->line('Please Provide Your Phone Number')?>", 'warning');
                      return;
                    }
                  }
                }
                
                

              }
            <?php endfor; ?>

          }


      }

      $(this).addClass('btn-progress');
      
      $("input:not([type=hidden])").each(function(){
        if($(this).is(":visible") == false)
          $(this).attr("disabled","disabled");
      });

      var queryString = new FormData($("#messenger_bot_form")[0]);
        $.ajax({
          context: this,
          type:'POST' ,
          url: base_url+"messenger_bot/edit_generate_messenger_bot",
          data: queryString,
          dataType : 'JSON',
          // async: false,
          cache: false,
          contentType: false,
          processData: false,
          success:function(response){
              $(this).removeClass('btn-progress');
              if(response.status=="1")
              {
                // $("#submit_status").addClass('alert alert-success').html(response.message);
                if(default_template == '0')
                {
                  var link="<?php echo $redirect_url; ?>";                 
                  swal('<?php echo $this->lang->line("Success"); ?>', response.message, 'success').then((value) => {
                    window.location.assign(link);
                  });                   
                }
                else if(default_template == 'postback')
                {
                  var link="<?php echo base_url('messenger_bot/template_manager'); ?>";                 
                  swal('<?php echo $this->lang->line("Success"); ?>', response.message, 'success').then((value) => {
                    window.location.assign(link);
                  });
                }
                else
                  swal('<?php echo $this->lang->line("Success"); ?>', response.message, 'success');

              }
              else
              {
                swal('<?php echo $this->lang->line("Error"); ?>', response.message, 'error');
              }
          }

        });

    });


    $('[data-toggle="popover"]').popover(); 
    $('[data-toggle="popover"]').on('click', function(e) {e.preventDefault(); return true;});

    $(document).on('click','.add_template',function(e){
        e.preventDefault();
        var current_id=$(this).prev().attr("id");
        var page_id="<?php echo $page_info['id'];?>";
        if(page_id=="")
        {
          alertify.alert('<?php echo $this->lang->line("Alert"); ?>',"<?php echo $this->lang->line('Please select a page first')?>",function(){});
          return false;
        }
        $("#add_template_modal").attr("current_id",current_id);
        $("#add_template_modal").modal();
      });

      $(document).on('click','.ref_template',function(e){
        e.preventDefault();
        var current_val=$(this).prev().prev().val();
        var current_id=$(this).prev().prev().attr("id");
        var page_id="<?php echo $page_info['id'];?>";
         if(page_id=="")
         {
           alertify.alert('<?php echo $this->lang->line("Alert"); ?>',"<?php echo $this->lang->line('Please select a page first')?>",function(){});
           return false;
         }
         $.ajax({
           type:'POST' ,
           url: base_url+"messenger_bot/get_postback",
           data: {page_id:page_id},
           success:function(response){
             $("#"+current_id).html(response).val(current_val);
           }
         });
      });

      $('#add_template_modal').on('hidden.bs.modal', function (e) { 
        var current_id=$("#add_template_modal").attr("current_id");
        var page_id="<?php echo $page_info['id'];?>";
         if(page_id=="")
         {
           alertify.alert('<?php echo $this->lang->line("Alert"); ?>',"<?php echo $this->lang->line('Please select a page first')?>",function(){});
           return false;
         }
         $.ajax({
           type:'POST' ,
           url: base_url+"messenger_bot/get_postback",
           data: {page_id:page_id},
           success:function(response){
             $("#"+current_id).html(response);
           }
         });
      });
  }); 
  


</script>


<div class="modal fade" id="add_template_modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-full">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('Add Template'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>      <div class="modal-body"> 
        <iframe src="" frameborder="0" width="100%" onload="resizeIframe(this)"></iframe>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" type="button" class="btn-lg btn btn-dark"><i class="fa fa-refresh"></i> <?php echo $this->lang->line("Close & Refresh List");?></button>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="error_modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-info"></i> <?php echo $this->lang->line('campaign error'); ?></h4>
      </div>
      <div class="modal-body">
        <div class="alert text-center alert-warning" id="error_modal_content">
          
        </div>
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


<div class="modal fade" id="media_template_modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> <?php echo $this->lang->line("How to get meida URL?"); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
      <div class="modal-body">
        <div>

          <p><?php echo $this->lang->line("To get the Facebook URL for an image or video, do the following:"); ?></p>
          <ul>
            <li><?php echo $this->lang->line("Click the image or video thumbnail to open the full-size view");?>.</li>
            <li><?php echo $this->lang->line("Copy the URL from your browser's address bar.<");?>/li>
          </ul>
          <p><?php echo $this->lang->line("Facebook URLs should be in the following base format:");?></p>
          <div class="table-responsive2">
            <table class='table table-condensed table-bordered table-hover table-striped' >
             <thead>
               <tr>
                 <th><?php echo $this->lang->line("Media Type");?></th>
                 <th><?php echo $this->lang->line("Media Source");?></th>
                 <th><?php echo $this->lang->line("URL Format");?></th>
               </tr>
             </thead>
             <thead>
               <tr>
                 <td><?php echo $this->lang->line("Video");?></td>
                 <td><?php echo $this->lang->line("Facebook Page");?></td>
                 <td>https://business.facebook.com/<b>PAGE_NAME</b>/videos/<b>NUMERIC_ID</b></td>
               </tr>
               <tr>
                 <td><?php echo $this->lang->line("Video");?></td>
                 <td><?php echo $this->lang->line("Facebook Account");?></td>
                 <td>https://www.facebook.com/<b>USERNAME</b>/videos/<b>NUMERIC_ID</b>/</td>
               </tr>
               <tr>
                 <td><?php echo $this->lang->line("Image");?></td>
                 <td><?php echo $this->lang->line("Facebook Page");?></td>
                 <td>https://business.facebook.com/<b>PAGE_NAME</b>/photos/<b>NUMERIC_ID</b></td>
               </tr>
               <tr>
                 <td><?php echo $this->lang->line("Image");?></td>
                 <td><?php echo $this->lang->line("Facebook Account");?></td>
                 <td>https://www.facebook.com/photo.php?fbid=<b>NUMERIC_ID</b></td>
               </tr>
             </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>