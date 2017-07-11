/** @param {jQuery} $ jQuery Object */

var postcomments_charLimit = 0; // Post Comment max characters

!function($, window, document, _undefined)
{
	/**
	 * Post Comments
	 *
	 * @param jQuery $textarea.PostCommentEditor
	 */
	XenForo.PostCommentEditor = function($input) { this.__construct($input); };
	XenForo.PostCommentEditor.prototype =
	{
		__construct: function($input)
		{
			this.$input = $input
				.keyup($.context(this, 'update'))
				.keydown($.context(this, 'preventNewline'));

			this.$counter = $(this.$input.data('postcommentcounter'));
			if (!this.$counter.length)
			{
				this.$counter = $('<span />').insertAfter(this.$input);
			}
			this.$counter
				.addClass('postCommentCounter')
				.text('0');

			this.$form = this.$input.closest('form').bind(
			{
				AutoValidationComplete: $.context(this, 'savePostComment')
			});

			this.charCount = 0; // number of chars currently in use

			this.update();
		},

		/**
		 * Handles key events on the post comment editor, updates the 'characters remaining' output.
		 *
		 * @param Event e
		 */
		update: function(e)
		{
			var postCommentText = this.$input.val();

			if (this.$input.attr('placeholder') && this.$input.attr('placeholder') == postCommentText)
			{
				this.setCounterValue(postcomments_charLimit, postCommentText.length);
			}
			else
			{
				this.setCounterValue(postcomments_charLimit - postCommentText.length, postCommentText.length);
			}
		},

		/**
		 * Sets the value of the character countdown, and appropriate classes for that value.
		 *
		 * @param integer Characters remaining
		 * @param integer Current length of post comment text
		 */
		setCounterValue: function(remaining, length)
		{
			if (remaining < 0)
			{
				this.$counter.addClass('error');
				this.$counter.removeClass('warning');
			}
			else if (remaining <= postcomments_charLimit - 130)
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
		 * Don't allow newline characters in the post comment.
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
				// $(this.$input.get(0).form).submit();

				return false;
			}
		},

		/**
		 * Updates the post comment field after saving
		 *
		 * @param event e
		 */
		savePostComment: function(e)
		{
			this.$input.val('');
			this.update(e);

			if (e.ajaxData && e.ajaxData.postcomment !== undefined)
			{
				$('.CurrentPostComment').text(e.ajaxData.postcomment);
			}
		}
	};

	XenForo.register('textarea.PostCommentEditor', 'XenForo.PostCommentEditor');
}
(jQuery, this, document);