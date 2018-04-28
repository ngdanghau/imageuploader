<?php
header('Content-type: text/html;charset=utf-8');

require_once './global.php';

$config = load_file('includes/config.php');
$options = $config['options'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Force latest IE rendering engine or ChromeFrame if installed -->
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
    <title>[ChipVN] Remote Image Uploader <?php echo VERSION;?></title>
    <meta charset="utf-8">
    <meta name="author" content="ptcong90 a.k.a chiplove.9xpro">
    <meta name="keywords" content="chipvn image uploader, image uploader, remote upload">
    <meta name="description" content="Upload images to Picasa, Flickr, ImageShack, Imgur..">
    <link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/js/jQuery-File-Upload-9.5.7/css/jquery.fileupload.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="title">Upload Photos</div>
                <i class="icon icon-cloud-upload"></i>
            </div><!--/.header-->
            <div class="content">
                <div class="options">
                    <div class="group">
                        <label>Upload from:</label>
                        <div class="control" id="upload-mode">
                            <a href="javascript:void(0);" class="active computer" data-mode="computer">Computer</a> |
                            <a href="javascript:void(0);" class="url" data-mode="url">URL</a>
                        </div>
                    </div>
                    <?php foreach ($options as $name => $option) : ?>

                        <div class="group<?php echo count($option['options']) <= 1 ? ' hide' : ''; ?>">
                            <label><?php echo $option['label'];?>:</label>
                            <div class="control">
                                <?php if (isset($option['type']) && $option['type'] == 'select') : ?>
                                    <span class="select">
                                        <select name="<?php echo $name;?>">
                                            <?php foreach ($option['options'] as $value => $text) : ?>
                                            <option<?php echo $option['default'] == $value ? ' selected="selected"': '';?> value="<?php echo $value;?>"><?php echo $text;?></option>
                                            <?php endforeach;?>
                                        </select>
                                        <i class="icon icon-caret-down"></i>
                                    </span>
                                <?php else :?>
                                    <?php foreach ($option['options'] as $value => $text) : ?>
                                    <span class="check">
                                        <i data-type="radio" data-name="<?php echo $name;?>" data-value="<?php echo $value;?>" class="icon <?php echo $option['default'] == $value ? 'icon-check-circle-o' : 'icon-circle-o'?>"></i> <?php echo $text;?>
                                    </span>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </div>
                        </div>

                    <?php endforeach;?>
                </div>
                <div class="upload-process"></div>
                <div class="transload-process">
                    <div class="input">
                        <textarea placeholder="Enter links here, each link separated by newline"></textarea>
                    </div>
                    <div class="list"></div>
                </div>
                <div id="results">
                    <div class="links">
                        <div class="tabs">
                            <div class="tab active" data-format="direct">Direct Link</div>
                            <div class="tab" data-format="bbcode">BB Code</div>
                            <div class="tab" data-format="html">HTML </div>
                        </div>
                        <textarea></textarea>
                    </div>
                </div><!--/#results-->
            </div><!--/.content-->
            <div class="content footer">
                <div class="group action upload">
                    <span class="button fileinput-button">
                        Add Files
                        <i class="icon icon-plus-circle"></i>
                        <input id="fileupload" type="file" name="files[]" multiple>
                    </span>
                    <button class="button green hide" id="upload">Upload <i class="icon icon-cloud-upload"></i></button>
                    <button class="button black hide" id="cancel-upload">Cancel <i class="icon icon-times-circle"></i></button>
                </div>
                <div class="group action transload">
                    <button class="button hide green" id="transload">Transload <i class="icon icon-cloud-upload"></i></button>
                    <button class="button hide black" id="cancel-transload">Cancel <i class="icon icon-times-circle"></i></button>
                </div>
                <div class="copyright">
                    Copyright &copy; 2010-2014 chiplove.9xpro
                </div>
            </div>
        </div><!--/.inner-->
    </div><!--/.wrapper-->
    <script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jQuery-File-Upload-9.5.7/js/vendor/jquery.ui.widget.js"></script>
    <script src="assets/js/jQuery-File-Upload-9.5.7/js/jquery.iframe-transport.js"></script>
    <script src="assets/js/jQuery-File-Upload-9.5.7/js/jquery.fileupload.js"></script>
    <script src="assets/js/imageuploader.js"></script>
    <script>
    CONFIG.ALLOWS_FILE_TYPES = /(<?php echo implode('|', $config['upload']['allow_file_types']);?>)$/i;
    CONFIG.MAX_FILE_SIZE = <?php echo $config['upload']['max_file_size'];?>;
    </script>
</body>
</html>

