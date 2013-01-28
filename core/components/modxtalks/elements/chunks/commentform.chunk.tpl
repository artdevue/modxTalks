<!-- Reply area -->
<div id="mt_cf_conversationReply">
<form action="[[+link]]" method="post" enctype="multipart/form-data">
<input type="hidden" name="token" value="df3bf4b6e6b7b">

<div class="post hasControls edit" id="reply">

<div class="avatar"><img src="[[+avatar]]" alt="" class="avatar "></div>

<div class="postContent thing">

<div class="postHeader">
<div class="info">
<h3>[[+write_comment]]</h3>
</div>
<div class="controls">
	<span class="formattingButtons">[[+controlsbb]]</span>
	<label class="previewCheckbox"><input type="checkbox" id="reply-previewCheckbox" onclick="MTConversation.togglePreview(&quot;reply&quot;,this.checked)" accesskey="p"> [[+previewCheckbox]]</label>
</div>
</div>

<div class="postBody">
	<textarea cols="200" rows="20" tabindex="200" name="content" class="text" style="height: 200px; overflow: hidden;" autocomplete="off"></textarea>
	<div id="reply-preview" class="preview"></div>
	<div class="editButtons"><input type="submit" name="postReply" value="[[+reply]]" class="big submit postReply button disabled" tabindex="300" disabled="disabled">
		<span id="reply-bg" class="buttonGroup[[+hidden]]">
			<input type="text" name="savename" value="" placeholder="[[+your_name_pl]]" class="saveName" />
			<input type="text" name="saveemail" value="" placeholder="[[+your_email_pl]]" class="saveEmail" />
		</span>
	</div>
</div>

</div>

</div>
</form></div>