/**
 * Copyright (c) 2014 Brivium (http://brivium.com)
 * Author: Brivium
 * Addon: Metadata Essential
 * Version: 1.6.5
 *
 * based on XenForo.StatusEditor
 */

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.BRMEMetaEditor = function($input) { this.__construct($input); };
	XenForo.BRMEMetaEditor.prototype =
	{
		__construct: function($input)
		{
			this.$input = $input
				.keyup($.context(this, 'update'));

			this.$counter = $(this.$input.data('addoneditorcounter'));
			if (!this.$counter.length)
			{
				this.$counter = $('<span />').insertAfter(this.$input);
			}
			this.$counter
				.addClass('brmeCounter')
				.text('0');

			this.charLimit = this.$input.attr('data-charlimit');
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
			else if (remaining <= this.charLimit - 25)
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
		}
	};

	XenForo.register('.BRMEMetaEditor', 'XenForo.BRMEMetaEditor');
}
(jQuery, this, document);