<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['course_id', 'chat_group_id', 'sender_id', 'shared_note_id', 'body', 'attachment_path', 'attachment_name', 'attachment_type'])]
class ChatMessage extends Model
{
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function chatGroup(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function sharedNote(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'shared_note_id');
    }
}
