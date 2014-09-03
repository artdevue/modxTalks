<!-- Reply area -->
<div id="mt_cf_conversationReply">
	<form action="[[+link]]" method="post" enctype="multipart/form-data">
		<div class="mt_post mt_hasControls mt_edit" id="mt_reply">
			<div class="mt_avatar"><img src="[[+avatar]]" alt="" class="mt_avatar_img" /></div>
			<div class="mt_postContent mt_thing">

				<div class="mt_postHeader">
					<div class="mt_info">
						<h3>[[+write_comment]]</h3>
					</div>
					<div class="mt_controls">
						<span class="mt_formattingButtons">[[+controlsbb]]</span>
						<label class="mt_previewCheckbox"><input type="checkbox" id="reply-previewCheckbox" onclick="MTConversation.togglePreview('mt_reply',this.checked)" accesskey="p"> [[+previewCheckbox]]</label>
					</div>
				</div>

				<div class="mt_postBody">
					<textarea tabindex="200" name="content" class="mt_text" style="height: 200px; overflow: hidden;" autocomplete="off"></textarea>

					<div id="mt_reply-preview" class="mt_preview"></div>
					<div class="mt_editButtons">
						<input type="submit" name="postReply" value="[[+reply]]" class="mt_big mt_submit mt_postReply mt_button mt_disabled" tabindex="300" disabled="disabled">
		<span id="mt_reply-bg" class="mt_buttonGroup[[+hidden]]">
			<input type="text" name="savename" value="" placeholder="[[+your_name_pl]]" class="mt_saveName"/>
			<input type="text" name="saveemail" value="" placeholder="[[+your_email_pl]]" class="mt_saveEmail"/>
		</span>
					</div>
				</div>

			</div>

		</div>
	</form>
</div>
