<div class="mt_mtComment" data-index="{$comment.index}" data-idx="{$comment.idx}">
    {$comment.timeMarker}
    <div class="mt_post mt_hasControls{$comment.deleted}" id="mt_comment-{$comment.id}" data-id="{$comment.id}" data-memberid="{$comment.userId}">
        <div class="mt_avatar"{$comment.hideAvatar}><img src="{$comment.avatar}" alt="" class="mt_avatar_img" /></div>
        <div class="mt_postContent mt_thing">
            <div class="mt_postHeader">
                <div class="mt_info">
                    <h3>{$comment.name}</h3>
                    <a href="{$comment.link}" class="mt_time" title="{$comment.date}">{$comment.funny_date}</a>
                    <span class="mt_group"></span>
                </div>
                <div class="mt_controls">
                    <span class="mt_editedBy"><span title="{$comment.funny_edit_date}">{$comment.funny_edit_date}</span> {$comment.edit_name}</span>
                    <a href="{$comment.link_reply}" title="{$comment.quote}" class="mt_control-quote">{$comment.quote}</a>
                    {$comment.user}
                </div>
            </div>
            <div class="mt_postBody">
                {$comment.content}
            </div>
        </div>
    </div>
</div>