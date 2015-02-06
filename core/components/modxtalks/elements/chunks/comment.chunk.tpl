<div class="mt_mtComment" data-index="[[+index]]" data-idx="[[+idx]]">
	[[+timeMarker]]
	<div class="mt_post mt_hasControls[[+deleted]]" id="comment-[[+id]]" data-id="[[+id]]" data-memberid="[[+userId]]">
		<div class="mt_avatar" [[+hideAvatar]]><img src="[[+avatar]]" alt="[[+name]]" class="mt_avatar_img"/></div>
		<div class="mt_postContent mt_thing">
			<div class="mt_postHeader">
				<div class="mt_info">
					<h3>[[+name]]</h3>
					<a href="[[+link]]" class="mt_time" title="[[+timeago]]">[[+funny_date]]</a>
					<span class="mt_group"></span>
				</div>
				<div class="mt_controls">
					<span class="mt_editedBy"><span title="[[+funny_edit_date]]">[[+funny_edit_date]]</span> [[+edit_name]]</span>
					<span class="mt_userBtn">
						<a href="#" onclick="MTConversation.commentQuote(this);return false" title="[[+quote]]" class="mt_icon mt_icon-quote">[[+quote]]</a>
						[[+user]]
					</span>
				</div>
			</div>
			<div class="mt_postBody">[[+content]]</div>
			[[+like_block]]
			[[+user_info]]
		</div>
	</div>
</div>
