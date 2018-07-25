/* {$___fileHash}
 * Part of '"{$___addOnTitle}"' for XenForo
 * Copyright © 2012-{$___currentYear} GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: {$___licenseLink}
 */

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    var GFNNotify = function($wrapper) { this.__construct($wrapper) };
    window.GFNNotify = GFNNotify;

    GFNNotify.prototype =
    {
        notifications: [],
        focused: true,
        queue: [],
        started: 0,

        __construct: function($wrapper)
        {
            this.wrapper = $wrapper;
            this.init();
        },

        init: function()
        {
            this.url = this.wrapper.data('url');
            this.timer = parseInt(this.wrapper.data('timer'));
            this.interval = parseInt(this.wrapper.data('interval'));

            var self = this;

            $.extend(true, XenForo,
            {
                stackAlert: function()
                {
                    self.call();
                }
            });

            this.call();

            $(window).focus(function()
            {
                this.focused = true;
            });

            $(window).blur(function()
            {
                this.focused = false;
            });
        },

        call: function()
        {
            var self = this;

            XenForo.ajax(this.url, {}, function(ajaxData)
            {
                if (typeof ajaxData.notifications != 'undefined')
                {
                    $.each(ajaxData.notifications, function(key, item)
                    {
                        self.queue.push(
                        {
                            id: key,
                            item: $(item)
                        });
                    });

                    self.process();
                }
            }, {global: false, error: false});
        },

        process: function()
        {
            var self = this;

            if (this.started)
            {
                return;
            }

            this.started = setInterval(function()
            {
                if (!self.focused)
                {
                    return;
                }

                if (self.queue.length < 1)
                {
                    clearInterval(self.started);
                    self.started = 0;
                    return;
                }

                var count = 0;

                self.queue = $.grep(self.queue, function(item)
                {
                    if (!self.focused)
                    {
                        return true;
                    }

                    setTimeout(function()
                    {
                        self.open(item.id, item.item);
                    }, count++ * self.interval);

                    return false;
                });
            }, 2000);
        },

        open: function($id, $item)
        {
            var self = this;
            this.notifications[$id] = {};
            this.notifications[$id].object = $item;

            $item.addClass('show');
            $item.appendTo(this.wrapper);
            this.initNotification($id, $item);

            self.notifications[$id].timeout = setTimeout(function()
            {
                self.close($id);
            }, self.timer);
        },

        close: function($id)
        {
            var notification = this.notifications[$id];

            if (notification)
            {
                clearTimeout(notification.timeout);
                notification.object.removeClass('show');

                setTimeout(function()
                {
                    notification.object.addClass('hide');

                    setTimeout(function()
                    {
                        notification.object.remove();
                    }, 1500);
                }, 25);

                this.notifications[$id] = null;
            }
        },

        initNotification: function($id, $item)
        {
            var self = this;
            var content = $item.find('.notificationText');
            var text = content.find('> p');

            text.css('margin-top', function()
            {
                return (content.height() - text.outerHeight()) / 2;
            });

            var href = text.find('.PopupItemLink').first().attr('href');

            $item.click(function(e)
            {
                e.preventDefault();
                self.close($id);

                XenForo.ajax(self.wrapper.data('mark-read'), {alert_id: $item.data('alert-id')}, function()
                {
                    XenForo.redirect(href);
                }, {global: false, error: false});
            });
        }
    };

    XenForo.register('#GFNNotification', 'GFNNotify');
}
(jQuery, this, document);