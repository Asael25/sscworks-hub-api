<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$wfs = Illuminate\Support\Facades\DB::table('approval_workflows')->get();
foreach ($wfs as $w) {
    echo "WF: ID={$w->id} | {$w->module} | {$w->process} | {$w->workflow_type}\n";
    $stgs = Illuminate\Support\Facades\DB::table('approval_workflow_stages')->where('workflow_id', $w->id)->get();
    foreach ($stgs as $s) {
        echo "   Stage {$s->layer_order}.{$s->sub_order}: {$s->name} | role={$s->role} | approver={$s->approver} | scope={$s->unit_scope}\n";
    }
}
unlink(__FILE__);
