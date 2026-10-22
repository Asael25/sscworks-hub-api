<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check tables related to workflows
$workflows = \Illuminate\Support\Facades\DB::table('universal_approval_workflows')->get();
foreach ($workflows as $wf) {
    echo "WF: ID={$wf->id} | Module={$wf->module} | Name={$wf->process_name} | Type={$wf->submission_type}\n";
    $stages = \Illuminate\Support\Facades\DB::table('universal_approval_workflow_stages')->where('workflow_id', $wf->id)->get();
    foreach ($stages as $st) {
        echo "   Stage {$st->layer_order}.{$st->sub_order}: {$st->layer_name} | role={$st->role} | approver={$st->approver} | unit_scope={$st->unit_scope}\n";
    }
}
unlink(__FILE__);
