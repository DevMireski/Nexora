<?php
namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    protected function log($event, $model)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $event,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'changes' => json_encode($model->getAttributes()),
        ]);
    }

    public function created($model)
    {
        $this->log('created', $model);
    }

    public function updated($model)
    {
        $this->log('updated', $model);
    }

    public function deleted($model)
    {
        $this->log('deleted', $model);
    }
}
