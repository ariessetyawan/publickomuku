/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined) {
    XenForo.StatusEditor = function($input) { this.__construct($input); };
    XenForo.StatusEditor.prototype =
    {
        __construct: function($input)
        {
            this.$input = $input
                .keyup($.context(this, 'update'))
                .keydown($.context(this, 'preventNewline'));

            this.$counter = $(this.$input.data('statuseditorcounter'));
            if (!this.$counter.length)
            {
                this.$counter = $('<span />').insertAfter(this.$input);
            }
            this.$counter
                .addClass('statusEditorCounter')
                .text('0');

            this.$form = this.$input.closest('form').bind(
                {
                    AutoValidationComplete: $.context(this, 'saveStatus')
                });

            this.charLimit = XenForo.ApplCharacterLimitOption; // Twitter max characters
            this.charCount = 0; // number of chars currently in use

            this.update();
        },

        /**
         * Handles key events on the status editor, updates the 'characters remaining' output.
         *
         * @param Event e
         */
        update: function(e)
        {
            var statusText = this.$input.val();

            if (this.$input.attr('placeholder') && this.$input.attr('placeholder') == statusText)
            {
                this.setCounterValue(this.charLimit, statusText.length);
            }
            else
            {
                this.setCounterValue(this.charLimit - statusText.length, statusText.length);
            }
        },

        /**
         * Sets the value of the character countdown, and appropriate classes for that value.
         *
         * @param integer Characters remaining
         * @param integer Current length of status text
         */
        setCounterValue: function(remaining, length)
        {
            if (remaining < 0)
            {
                this.$counter.addClass('error');
                this.$counter.removeClass('warning');
            }
            else if (remaining <= this.charLimit - 130)
            {
                this.$counter.removeClass('error');
                this.$counter.addClass('warning');
            }
            else
            {
                this.$counter.removeClass('error');
                this.$counter.removeClass('warning');
            }

            this.$counter.text(remaining);
            this.charCount = length || this.$input.val().length;
        },

        /**
         * Don't allow newline characters in the status message.
         *
         * Submit the form if [Enter] or [Return] is hit.
         *
         * @param Event e
         */
        preventNewline: function(e)
        {
            if (e.which == 13) // return / enter
            {
                e.preventDefault();

                $(this.$input.get(0).form).submit();

                return false;
            }
        },

        /**
         * Updates the status field after saving
         *
         * @param event e
         */
        saveStatus: function(e)
        {
            this.$input.val('');
            this.update(e);

            if (e.ajaxData && e.ajaxData.status !== undefined)
            {
                $('.CurrentStatus').text(e.ajaxData.status);
            }
        }
    };
}(jQuery, this, document);