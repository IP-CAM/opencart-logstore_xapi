<!-- admin/view/template/extension/module/logstore_xapi.tpl -->
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-recent-products" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form method="post" enctype="multipart/form-data" id="form-recent-products" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-endpoint"><?php echo $entry_endpoint; ?></label>
            <div class="col-sm-10">
              <input type="text" name="logstore_xapi_endpoint" value="<?php echo $logstore_xapi_endpoint; ?>" placeholder="<?php echo $entry_endpoint; ?>" id="input-endpoint" class="form-control" />
              <?php if ($error_endpoint) { ?>
              <div class="text-danger"><?php echo $error_endpoint; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_username; ?></label>
            <div class="col-sm-10">
              <input type="text" name="logstore_xapi_username" value="<?php echo $logstore_xapi_username; ?>" placeholder="<?php echo $entry_username; ?>" id="input-username" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_password; ?></label>
            <div class="col-sm-10">
              <input type="text" name="logstore_xapi_password" value="<?php echo $logstore_xapi_password; ?>" placeholder="<?php echo $entry_password; ?>" id="input-password" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_max_batch_size; ?></label>
            <div class="col-sm-10">
              <input type="text" name="logstore_xapi_max_batch_size" value="<?php echo $logstore_xapi_max_batch_size; ?>" placeholder="<?php echo $entry_max_batch_size; ?>" id="input-max_batch_size" class="form-control" />
              <?php if ($error_max_batch_size) { ?>
              <div class="text-danger"><?php echo $error_max_batch_size; ?></div>
              <?php } ?>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>