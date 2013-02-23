<!-- Reply area -->
<div class="mt_standalone">
<form action="[[+link]]" method="mt_post" enctype="multipart/form-data">
<input type="hidden" name="token" value="df3bf4b6e6b7b">

<div class="mt_post mt_hasControls mt_edit" id="mt_comment-[[+id]]">

<div class="mt_avatar"><img src="[[+avator]]" alt="" class="mt_avatar_img"></div>

<div class="mt_postContent mt_thing">

<div class="mt_postHeader">
<div class="mt_info">
<h3>[[+write_comment]]</h3>
</div>
<div class="mt_controls">
	<span class="mt_formattingButtons">[[+controlsbb]]</span>
	<label class="mt_previewCheckbox"><input type="checkbox" id="mt_comment-[[+id]]-previewCheckbox" onclick="MTConversation.togglePreview(&quot;comment-[[+id]]&quot;,this.checked)" accesskey="p"> [[+previewCheckbox]]</label>
</div>
</div>

<div class="mt_postBody">
	<textarea cols="200" rows="20" tabindex="200" name="content" class="mt_text" style="height: 200px; overflow: hidden;" autocomplete="off">[[+content]]</textarea>
	<div id="mt_comment-[[+id]]-preview" class="mt_preview"></div>
	<div class="mt_editButtons">
		<input type="submit" name="save" value="[[+save_changes]]" class="mt_big mt_submit mt_button">
		<input type="submit" name="cancel" value="[[+cancel]]" class="mt_big mt_cancel mt_button">
	</div>
</div>

</div>

</div>
</form></div>