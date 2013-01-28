<!-- Reply area -->
<div class="standalone">
<form action="[[+link]]" method="post" enctype="multipart/form-data">
<input type="hidden" name="token" value="df3bf4b6e6b7b">

<div class="post hasControls edit" id="comment-[[+id]]">

<div class="avatar"><img src="[[+avator]]" alt="" class="avatar "></div>

<div class="postContent thing">

<div class="postHeader">
<div class="info">
<h3>[[+write_comment]]</h3>
</div>
<div class="controls">
	<span class="formattingButtons">[[+controlsbb]]</span>
	<label class="previewCheckbox"><input type="checkbox" id="comment-[[+id]]-previewCheckbox" onclick="MTConversation.togglePreview(&quot;comment-[[+id]]&quot;,this.checked)" accesskey="p"> [[+previewCheckbox]]</label>
</div>
</div>

<div class="postBody">
	<textarea cols="200" rows="20" tabindex="200" name="content" class="text" style="height: 200px; overflow: hidden;" autocomplete="off">[[+content]]</textarea>
	<div id="comment-[[+id]]-preview" class="preview"></div>
	<div class="editButtons">
		<input type="submit" name="save" value="[[+save_changes]]" class="big submit button">
		<input type="submit" name="cancel" value="[[+cancel]]" class="big cancel button">
	</div>
</div>

</div>

</div>
</form></div>