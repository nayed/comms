<?php

namespace Comms\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];

    protected $hidden = ['email', 'ip'];

    protected $appends = ['email_md5', 'ip_md5'];

    protected static $commentable_for = ['Post', 'Tutorial'];

    public function getEmailMd5Attribute()
    {
        return md5($this->attributes['email']);
    }

    public function getIpMd5Attribute()
    {
        return md5($this->attributes['ip']);
    }

    public static function allFor($model, $model_id)
    {
        $comments = [];
        $by_id = [];

        $records = self::where([
            'commentable_id' => $model_id,
            'commentable_type' => $model
        ])->orderBy('created_at', 'ASC')->get();

        foreach ($records as $record) {
            if ($record->reply) {
                $by_id[$record->reply]->attributes['replies'][] = $record;
            }
            else {
                $record->attributes['replies'] = [];
                $by_id[$record->id] = $record;
                $comments[] = $record;
            }
        }

        return array_reverse($comments);
    }

    public static function isCommentable($model, $model_id)
    {
        if (!in_array($model, self::$commentable_for)) {
            return false;
        }
        else {
            $model = "\\Comms\\Models\\$model";
            return $model::where(['id' => $model_id])->exists();
        }
    }
}
