<div class="mtComment" data-index="{$comment.index}" data-idx="{$comment.idx}">
    {$comment.timeMarker}
    <div class="post hasControls{$comment.deleted}" id="comment-{$comment.id}" data-id="{$comment.id}" data-memberid="{$comment.userId}">
        <div class="avatar"{$comment.hideAvatar}><img src="{$comment.avatar}" alt="" class="avatar" /></div>
        <div class="postContent thing">
            <div class="postHeader">
                <div class="info">
                    <h3>{$comment.name}</h3>
                    <a href="{$comment.link}" class="time" title="{$comment.date}">{$comment.funny_date}</a>
                    <span class="group"></span>
                </div>
                <div class="controls">
                    <span class="editedBy"><span title="{$comment.funny_edit_date}">{$comment.funny_edit_date}</span> {$comment.edit_name}</span>
                    <a href="{$comment.link_reply}" title="{$comment.quote}" class="control-quote">{$comment.quote}</a>
                    {$comment.user}
                </div>
            </div>
            <div class="postBody">
                {$comment.content}
            </div>
        </div>
    </div>
</div>