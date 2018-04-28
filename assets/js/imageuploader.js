/**
 * ChipVN Image Uploader
 *
 * @copyright (c) 2010-2014 Phan Thanh Cong <ptcong90@gmail.com>
 * @version 5.1
 * @released Apr 12, 2014
 */
var CONFIG = {
    SERVER_URL                       : 'upload.php',
    ALLOWS_FILE_TYPES                : /(jpg|jpeg|jpg|png|gif)$/, // will be overrided by index.php
    MAX_FILE_SIZE                    : 2*1024*1024,               // will be overrided by index.php
    SHOW_RETRY_WHEN_UPLOAD_FAILED    : true,
    SHOW_RETRY_WHEN_TRANSLOAD_FAILED : true,
    SHOW_RETRY_TIMES                 : 3
};
var TEXT = {
    JUST_ADDED      : 'Just added',
    QUEUE_TO_UPLOAD : 'Queue to upload',
    UPLOAD_ERROR    : 'An error occured while uploading',
    TRANSLOAD_ERROR : 'An error occured while transloading'
};

var ImageUploader = {
    init: function() {
        this.registerOptions();
        ImageUploader.Upload.init();
        ImageUploader.Transload.init();
    },
    registerOptions: function() {
        // radio
        $('.options .control .icon[data-name]').each(function() {
            var $icon = $(this);
            $icon.parent('.check').click(function() {
                $icon
                    .removeClass('icon-circle-o')
                    .addClass('icon-check-circle-o')
                    .attr('data-checked', 1);

                $icon.parents('.control')
                    .find('.check .icon')
                    .not($icon)
                    .removeClass('icon-check-circle-o')
                    .addClass('icon-circle-o')
                    .removeAttr('data-checked');
            });
            if ($icon.hasClass('icon-check-circle-o')) {
                $icon.attr('data-checked', 1);
            }
        });
        // toggle mode
        $('#upload-mode a').click(function() {
            if ($(this).data('mode') == 'computer') {
                $('.upload-process').slideDown();
                $('.transload-process').slideUp();
                $('.footer .action.upload').show();
                $('.footer .action.transload').hide();
                $(this).addClass('active');
                $('#upload-mode .url').removeClass('active');
                ImageUploader.Transload.cancel();

            } else {
                $('.upload-process').slideUp();
                $('.transload-process').slideDown();
                $('.footer .action.upload').hide();
                $('.footer .action.transload').show();
                $(this).addClass('active');
                $('#upload-mode .computer').removeClass('active');
                ImageUploader.Upload.cancel();
            }
            ImageUploader.Upload.hideResults();
        });
    },
    getOption: function(name) {
        var $element = $('[data-name="' + name + '"].icon-check-circle-o');
        return $element.length
            ? $element.data('value')
            : ($('[name="' + name + '"]').length
                ? $('[name="' + name + '"]').val()
                : null);
    },
    updateLayout: function() {
        var $container = $('.wrapper .container'),
            marginTop = -$container.outerHeight() / 2;
        $container.animate({
            'margin-top': marginTop
        }, 150);
    }
};

// UPLOAD
ImageUploader.Upload = {
    _itemsAdded           : {},
    _isProcessing         : false,
    _itemsSelector        : '.upload-process',
    _resultsSelector      : '#results .links',
    _resultsInputSelector : '#results .links textarea',
    _xhr                  : null,
    init: function() {
        this.registerActions();
        this.setupFileUploader();
    },
    setupFileUploader: function() {
        $('#fileupload').fileupload({
            url: CONFIG.SERVER_URL,
            dataType: 'json',
            autoUpload: false,
            sequentialUploads: true,
            limitConcurrentUploads: 1
        })
        .bind('fileuploadadd', function (e, data) {
            ImageUploader.Upload.add(data);
        })
        .bind('fileuploadsubmit', function (e, data) {
            data.formData = {
                type               : 'upload',
                watermark          : ImageUploader.getOption('watermark'),
                watermark_position : ImageUploader.getOption('watermark_position'),
                watermark_logo     : ImageUploader.getOption('watermark_logo'),
                resize             : ImageUploader.getOption('resize'),
                server             : ImageUploader.getOption('server')
            };
        });
    },
    registerActions: function() {
        $('#upload').click($.proxy(this.queueProcess, this));
        $('#cancel-upload').click($.proxy(this.cancel, this));
    },
    cancel: function(){
        if (this._xhr) this._xhr.abort();
        $(this._itemsSelector + ' .item').remove();
        this._isProcessing = false;
        this._itemsAdded = {};
        this.updateButtons();
        this.hideResults();
        $('#fileupload').stop();
    },
    queueProcess: function() {
        var me = this,
            $item,
            $waitingItems = me.getWaitingItems();
        if (this._isProcessing) return false;

        $(me._itemsSelector + ' .item.waiting .result input').val(TEXT.QUEUE_TO_UPLOAD);

        if (($item = $waitingItems.first()) && $item.length) {
            $(me._itemsSelector).animate({scrollTop: $item.outerHeight() * $item.index()}, 1000);

            me._isProcessing = true;
            $item.removeClass('waiting').addClass('uploading');

            $('.result .loading', $item).show();
            $('.result input', $item).hide();
            $('.status .icon', $item)
                .removeClass('icon-picture-o')
                .addClass('icon-spinner');

            var handleError = function($item, result) {
                $item.hide();
                $item.removeClass('waiting uploading').addClass('uploadfail');
                $('.result .loading', $item).hide();
                $('.result input', $item).show().val(result.message || TEXT.UPLOAD_ERROR);
                $('.status .icon', $item)
                    .removeClass('icon-spinner')
                    .addClass('icon-minus-circle');

                var failed = $('.refresh').data('failed') || 1;
                if (failed < CONFIG.SHOW_RETRY_TIMES && CONFIG.SHOW_RETRY_WHEN_UPLOAD_FAILED) {
                    $('.remove', $item).hide();
                    $('.refresh', $item).show()
                        .data('failed', failed+1)
                        .unbind()
                        .click(function() {
                            $item.removeClass('uploading uploadfail').addClass('waiting');
                            me.queueProcess();
                        });
                } else {
                    $('.remove', $item).show();
                    $('.refresh', $item).hide();
                }
                $item.fadeIn();
            };
            me._xhr = $item
                .data('data')
                .submit()
                .success(function(result, textStatus, jqXHR) {
                    if (result.error) {
                        return handleError($item, result);
                    }
                    $item.hide();
                    $item.removeClass('waiting uploading').addClass('uploaded');
                    $('.result .loading', $item).hide();
                    $('.result input', $item).show().val(result.url)
                        .removeClass('readonly')
                        .removeAttr('readonly');

                    $('.status .icon', $item)
                        .removeClass('icon-spinner icon-minus-circle')
                        .addClass('icon-check-square-o');

                    $('.remove, .refresh', $item).remove();
                    $item.fadeIn();

                }).error(function(jqXHR, textStatus, errorThrown) {
                    handleError($item, {});
                }).complete(function(result, textStatus, jqXHR) {
                    me._isProcessing = false;
                    me.queueProcess();
                    me.showResults(me.getResults());
                });
        }
        me.updateButtons();
    },
    updateButtons: function() {
        var hasWaitingItems = this.getWaitingItems().length > 0;
        if (hasWaitingItems && !this._isProcessing) {
            $('#upload').fadeIn();
        } else {
            $('#upload').hide();
        }
        if (hasWaitingItems || Object.keys(this._itemsAdded).length > 0) {
            $('#cancel-upload').fadeIn();
        } else {
            $('#cancel-upload').hide();
        }
    },
    getWaitingItems: function() {
        return $(this._itemsSelector + ' .item.waiting');
    },
    getResults: function() {
        var links = [];
        $(this._itemsSelector + ' .result input').not('.readonly').not(':disabled').each(function() {
            links.push($(this).val());
        });
        return links;
    },
    showResults: function(links) {
        var me = this;
        var $links = $(me._resultsSelector).hide();
        if (links && links.length) {
            $(me._resultsInputSelector).unbind().focus(function(){
                this.select();
            });
            var format = $('.tabs .tab.active', $links).data('format');
            $(me._resultsInputSelector).val(me.formatResults(links, format));

            $links.show();
        }
        $('.tab', $links).unbind().click(function(){
            var $this = $(this),
                format = $this.data('format');
            $this.parents('.tabs').find('.tab').removeClass('active');
            $this.addClass('active');
            $(me._resultsInputSelector).val(me.formatResults(links, format));
        });
        ImageUploader.updateLayout();
    },
    hideResults: function() {
        $(this._resultsSelector).slideUp();
        $(this._resultsInputSelector).val('');
        setTimeout(ImageUploader.updateLayout, 300);
    },
    formatResults: function(links, format) {
        var wrapper;
        if (format == 'direct') {
            wrapper = ['', ''];
        } else if(format == 'bbcode') {
            wrapper = ['[img]', '[/img]'];
        }  else if(format == 'html') {
            wrapper = ['<img src="', '" />'];
        }
        return wrapper[0] + links.join(wrapper[1] + "\n" + wrapper[0]) + wrapper[1];
    },
    add: function(data) {
        var me = this,
            file = data.files[0],
            identifier = file.name + file.lastModifiedDate;

        // filter
        if (typeof me._itemsAdded[identifier] != 'undefined'
            || !CONFIG.ALLOWS_FILE_TYPES.test(file.name)
            || CONFIG.MAX_FILE_SIZE < file.size
        ) {
            return me.updateButtons();
        }

        var $item = $('<div class="item waiting"> \
            <span class="status"><i class="icon icon-picture-o"></i></span> \
            <span class="name"><input class="transparent" value="' + file.name + '" /></span> \
            <span class="result"> \
                <input class="transparent readonly" readonly="readonly" value="' + TEXT.JUST_ADDED + '"> \
                <img class="loading" src="assets/images/loading5.gif" /> \
            </span> \
            <span class="remove"><i class="icon icon-times-circle"></i></span> \
            <span class="refresh"><i class="icon icon-refresh"></i></span> \
        </div>');

        $(me._itemsSelector).append($item);

        $item.hide();
        $item.data('identifier', identifier);
        $item.data('data', data);
        $item.fadeIn();
        // map
        me._itemsAdded[identifier] = true;
        me.registerItemEvents();
        me.updateButtons();
    },
    registerItemEvents: function() {
        var me = this;
        $(me._itemsSelector + ' .remove').unbind().click(function() {
            var $item = $(this).parent('.item'),
                identifier = $item.data('identifier');
            // remove map
            delete me._itemsAdded[identifier];
            $item.remove();
            if (me._isProcessing) {
                me._isProcessing = false;
                me.queueProcess();
            } else {
                me.updateButtons();
            }
        });
        $(me._itemsSelector + '.item input').unbind().focus(function(){
            this.select();
        });
        ImageUploader.updateLayout();
    }
};

// TRANSLOAD
ImageUploader.Transload = $.extend({}, ImageUploader.Upload, {
    _processSelector: '.transload-process',
    _itemsSelector : '.transload-process .list',
    init: function() {
        this.registerActions();
    },
    registerActions: function() {
        var me = this;
        $(me._processSelector + ' .input textarea').change(function(){
            me.applyInputLinks();
            me.updateButtons();
        });
        $('#transload').click($.proxy(this.queueProcess, this));
        $('#cancel-transload').click($.proxy(this.cancel, this));
    },
    cancel: function() {
        if(this._xhr) this._xhr.abort();
        $(this._processSelector + ' .input textarea').val('');
        $(this._itemsSelector + ' .item').remove();
        this.hideResults();
        this._itemsAdded = {};
        this._isProcessing = false;
    },
    queueProcess: function() {
        var me = this,
            $item,
            $waitingItems = me.getWaitingItems();

        if (this._isProcessing) return false;

        $(me._itemsSelector + ' .item.waiting .result input').val(TEXT.QUEUE_TO_UPLOAD);

        if (($item = $waitingItems.first()) && $item.length) {
            $(me._itemsSelector).animate({scrollTop: $item.outerHeight() * $item.index()}, 1000);

            me._isProcessing = true;
            $item.removeClass('waiting').addClass('uploading');

            $('.result .loading', $item).show();
            $('.result input', $item).hide();
            $('.status .icon', $item)
                .removeClass('icon-link')
                .addClass('icon-spinner');


            var handleError = function($item, result) {
                $item.hide();
                $item.removeClass('waiting uploading').addClass('uploadfail');
                $('.result .loading', $item).hide();
                $('.result input', $item).show().val(result.message || TEXT.TRANSLOAD_ERROR);
                $('.status .icon', $item)
                    .removeClass('icon-spinner')
                    .addClass('icon-minus-circle');

                var failed = $('.refresh').data('failed') || 1;
                if (failed < CONFIG.SHOW_RETRY_TIMES && CONFIG.SHOW_RETRY_WHEN_TRANSLOAD_FAILED) {
                    $('.remove', $item).hide();
                    $('.refresh', $item).show()
                        .data('failed', failed+1)
                        .unbind()
                        .click(function() {
                            $item.removeClass('uploading uploadfail').addClass('waiting');
                            me.queueProcess();
                        });
                } else {
                    $('.remove', $item).show();
                    $('.refresh', $item).hide();
                }
                $item.fadeIn();
            };

            me._xhr = $.ajax({
                url: CONFIG.SERVER_URL,
                dataType: 'json',
                type: 'POST',
                data: {
                    url  : $item.data('link'),
                    type : 'transload',
                    watermark          : ImageUploader.getOption('watermark'),
                    watermark_position : ImageUploader.getOption('watermark_position'),
                    watermark_logo     : ImageUploader.getOption('watermark_logo'),
                    resize             : ImageUploader.getOption('resize'),
                    server             : ImageUploader.getOption('server')
                }
            })
            .success(function(result, textStatus, jqXHR) {
                if (result.error) {
                    return handleError($item, result);
                }
                $item.hide();
                $item.removeClass('waiting uploading').addClass('uploaded');
                $('.result .loading', $item).hide();
                $('.result input', $item).show().val(result.url)
                        .removeClass('readonly')
                        .removeAttr('readonly');
                $('.status .icon', $item)
                    .removeClass('icon-spinner icon-minus-circle')
                    .addClass('icon-check-square-o');

                $('.remove', $item).remove();
                $item.fadeIn();
            }).error(function(jqXHR, textStatus, errorThrown) {
                handleError($item, {});
            }).complete(function(result, textStatus, jqXHR) {
                me._isProcessing = false;
                me.queueProcess();
                me.showResults(me.getResults());
            });
        }
    },
    updateButtons: function() {
        if (this.getWaitingItems().length > 0 || Object.keys(this._itemsAdded).length > 0) {
            $('#transload, #cancel-transload').show();
        } else {
            $('#transload, #cancel-transload').hide();
        }
    },
    applyInputLinks: function() {
        var me = this,
            text = $(me._processSelector + ' .input textarea').val(),
            re = /.*?(\[IMG\])?(https?[^\\\n[]+)(\[\/IMG\])?/ig,
            m, i = 0, link;
        while (m = re.exec(text)) {
            link = $.trim(m[2]);
            if (typeof me._itemsAdded[link] == 'undefined') {

                var $item = $('<div class="item waiting"> \
                    <span class="status"><i class="icon icon-link"></i></span> \
                    <span class="name"><input class="transparent" value="' + link + '" /></span> \
                    <span class="result"> \
                        <input class="transparent readonly" readonly="readonly" value="' + TEXT.JUST_ADDED + '"> \
                        <img class="loading" src="assets/images/loading5.gif" /> \
                    </span> \
                    <span class="remove"><i class="icon icon-times-circle"></i></span> \
                    <span class="refresh"><i class="icon icon-refresh"></i></span> \
                </div>');

                me._itemsAdded[link] = true;
                $(me._itemsSelector).append($item);

                $item.data('link', link).hide().fadeIn();
            }
        }
        $(me._processSelector + ' textarea').val('');
        me.registerItemEvents();
    }
});


(function(){
    ImageUploader.init();
})();
