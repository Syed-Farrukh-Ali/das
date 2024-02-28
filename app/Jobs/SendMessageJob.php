<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sms_typ_id;
    protected $mobile;
    protected $message;

    public function __construct($sms_typ_id, $mobile, $message)
    {
        $this->sms_typ_id = $sms_typ_id;
        $this->mobile = $mobile;
        $this->message = $message;
    }

    public function handle()
    {
        _sendSMS($this->sms_typ_id, $this->mobile, $this->message);
    }
}
