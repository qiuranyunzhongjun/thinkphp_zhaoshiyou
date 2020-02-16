$(function () {
    //ajax get请求
    $('.ajax-get').click(function () {
        var target;
        var that = this;
        if ($(this).hasClass('confirm')) {
            if (!confirm('确认要执行该操作吗?')) {
                return false;
            }
        }
        if ((target = $(this).attr('href')) || (target = $(this).attr('url'))) {
            $(that).addClass('disabled').prop('disabled', true);
            $.get(target).success(function (data) {
                if (data.status == 1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~', 'alert-success');
                    } else {
                        updateAlert(data.info, 'alert-success');
                    }
                    setTimeout(function () {
                        if (data.url) {
                            location.href = data.url;
                        } else if ($(that).hasClass('no-refresh')) {
                            //
                        } else {
                            location.reload();
                        }
                    }, 1500);
                } else {
                    $(that).addClass('disabled').prop('disabled', false);
                    updateAlert(data.info, 'alert-error');
                    setTimeout(function () {
                        if (data.url) {
                            location.href = data.url;
                        } else {
                            //
                        }
                    }, 1500);
                }
            });
        }
        return false;
    });
    $("form").on("submit", function (ev) {
        var that = this;
        if ($(that).hasClass('no-ajax')) {
            return true;
        }
        var options = {
            type: 'post',
            dataType: 'json',
            success: function (data) {
                if (data.status == 1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~', 'alert-success');
                    } else {
                        updateAlert(data.info, 'alert-success');
                    }
                    setTimeout(function () {
                        if (data.url) {
                            location.href = data.url;
                        } else if ($(that).hasClass('no-refresh')) {
                            //$('.alert').hide();
                        } else {
                            location.reload();
                        }
                    }, 1500);
                } else {
                    if( $(".reloadverify").length>0 ){
                        $(".reloadverify").click();
                    }
                    updateAlert(data.info, 'alert-error');
                    $(that).find('[type=submit]').removeClass('disabled').prop('disabled', false);
                    setTimeout(function () {
                        if (data.url) {
                            location.href = data.url;
                        } else {
                            //$('.alert').hide();
                        }
                    }, 1500);
                }
            },
            error: function () {
                updateAlert('发生错误', 'alert-error');
                $(that).find('[type=submit]').removeClass('disabled').prop('disabled', false);
            }
        };
        $(that).find('[type=submit]').addClass('disabled').attr('autocomplete', 'off').prop('disabled', true);
        $(that).ajaxSubmit(options);
        return false;
    });

    window.updateAlert = function (text, c) {
        text = text || 'default';
        c = c || false;
        if (c != false) {
            if (c == 'alert-success') {
                layer.msg(text, {icon: 1});
            } else if (c == 'alert-error') {
                layer.msg(text, {icon: 2});
            } else if (c == 'alert-warning') {
                layer.msg(text, {icon: 4});
            } else {
                layer.msg(text, {icon: 3});
            }
        }
    };

//全选的实现
    $(".check-all").click(function () {
        $(".check-all").prop("checked", this.checked);
        $(".ids").prop("checked", this.checked);
    });
    $(".ids").click(function () {
        var option = $(".ids");
        option.each(function (i) {
            if (!this.checked) {
                $(".check-all").prop("checked", false);
                return false;
            } else {
                $(".check-all").prop("checked", true);
            }
        });
    });

//ajax post submit请求
    $('.ajax-post').click(function () {
        var target, query, form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm = false;
        if (($(this).attr('type') == 'submit') || (target = $(this).attr('href')) || (target = $(this).attr('url'))) {
            form = $('.' + target_form);
            if ($(this).attr('hide-data') === 'true') {//无数据时也可以使用的功能
                form = $('.hide-data');
                query = form.serialize();
            } else if (form.get(0) == undefined) {
                return false;
            } else if (form.get(0).nodeName == 'FORM') {
                if ($(this).hasClass('confirm')) {
                    if (!confirm('确认要执行该操作吗?')) {
                        return false;
                    }
                }
                if ($(this).attr('url') !== undefined) {
                    target = $(this).attr('url');
                } else {
                    target = form.get(0).action;
                }
                query = form.serialize();
            } else if (form.get(0).nodeName == 'INPUT' || form.get(0).nodeName == 'SELECT' || form.get(0).nodeName == 'TEXTAREA') {
                form.each(function (k, v) {
                    if (v.type == 'checkbox' && v.checked == true) {
                        nead_confirm = true;
                    }
                });
                if (nead_confirm && $(this).hasClass('confirm')) {
                    if (!confirm('确认要执行该操作吗?')) {
                        return false;
                    }
                }
                query = form.serialize();
            } else {
                if ($(this).hasClass('confirm')) {
                    if (!confirm('确认要执行该操作吗?')) {
                        return false;
                    }
                }
                query = form.find('input,select,textarea').serialize();
            }
            $(that).addClass('disabled').attr('autocomplete', 'off').prop('disabled', true);
            $.post(target, query).success(function (data) {
                if (data.status == 1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~', 'alert-success');
                    } else {
                        updateAlert(data.info, 'alert-success');
                    }
                    setTimeout(function () {
                        if (data.url) {
                            location.href = data.url;
                        } else if ($(that).hasClass('no-refresh')) {
                            //
                        } else {
                            location.reload();
                        }
                    }, 1500);
                } else {
                    updateAlert(data.info,'alert-error');
                    setTimeout(function () {
                        $(that).removeClass('disabled').prop('disabled', false);
                        if (data.url) {
                            location.href = data.url;
                        } else {
                            //
                        }
                    }, 1500);
                }
            });
        }
        return false;
    });

    $('.search-get').on('click', function () {
        var input_arr = $(this).parent().parent().find('input');
        var url = $(this).attr('uri');
        var params = '';
        $.each(input_arr, function (k, v) {
            var value = $(v).val();
            if (value != '') {
                params += '&' + $(v).attr('name') + '=' + value;
            }
        });
        if (typeof (url) != "undefined" || url != 'null' || url != '') {
            window.location.href = url + params;
        } else {
            return false;
        }
    });

})